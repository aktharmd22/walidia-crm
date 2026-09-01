import { useEffect, useRef, useState } from 'react'
import { router } from '@inertiajs/react'
import * as Dialog from '@radix-ui/react-dialog'
import { Search } from 'lucide-react'
import { cn } from '@/lib/cn'

interface SearchHit {
  type: string
  label: string
  subtitle: string | null
  href: string
}

/**
 * ⌘K palette. Results are grouped by record type and come from the server,
 * which applies the same ownership scope as every other query (D-017) —
 * search is not a back door around visibility.
 */
export function GlobalSearch({ open, onOpenChange }: { open: boolean; onOpenChange: (open: boolean) => void }) {
  const [query, setQuery] = useState('')
  const [hits, setHits] = useState<SearchHit[]>([])
  const [loading, setLoading] = useState(false)
  const [active, setActive] = useState(0)
  const abort = useRef<AbortController | null>(null)

  useEffect(() => {
    if (!open) {
      setQuery('')
      setHits([])
      setActive(0)
    }
  }, [open])

  useEffect(() => {
    if (query.trim().length < 2) {
      setHits([])
      return
    }

    const timer = window.setTimeout(async () => {
      abort.current?.abort()
      abort.current = new AbortController()
      setLoading(true)
      try {
        const response = await fetch(`/search/suggest?q=${encodeURIComponent(query)}`, {
          headers: { Accept: 'application/json' },
          signal: abort.current.signal,
        })
        if (response.ok) {
          const payload = (await response.json()) as { hits: SearchHit[] }
          setHits(payload.hits)
          setActive(0)
        }
      } catch {
        /* aborted or offline — the empty state below covers it */
      } finally {
        setLoading(false)
      }
    }, 200)

    return () => window.clearTimeout(timer)
  }, [query])

  function go(hit: SearchHit) {
    onOpenChange(false)
    router.visit(hit.href)
  }

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-modal bg-ink/40" />
        <Dialog.Content className="fixed inset-x-4 top-[12vh] z-modal mx-auto w-auto max-w-[560px] overflow-hidden rounded-shell border border-line bg-hull shadow-modal focus:outline-none">
          <Dialog.Title className="sr-only">Search</Dialog.Title>

          <div className="flex items-center gap-3 border-b border-line px-4">
            <Search className="size-4 text-ink-faint" aria-hidden />
            <input
              autoFocus
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              onKeyDown={(event) => {
                if (event.key === 'ArrowDown') {
                  event.preventDefault()
                  setActive((index) => Math.min(index + 1, hits.length - 1))
                }
                if (event.key === 'ArrowUp') {
                  event.preventDefault()
                  setActive((index) => Math.max(index - 1, 0))
                }
                if (event.key === 'Enter' && hits[active]) {
                  event.preventDefault()
                  go(hits[active])
                }
              }}
              placeholder="Search clients, yachts, bookings, listings…"
              aria-label="Search"
              className="h-12 flex-1 border-0 bg-transparent text-body text-ink placeholder:text-ink-faint focus:outline-none"
            />
          </div>

          <div className="max-h-[50vh] overflow-y-auto p-2">
            {loading && <p className="px-3 py-4 text-small text-ink-faint">Searching…</p>}

            {!loading && query.length >= 2 && hits.length === 0 && (
              <p className="px-3 py-4 text-small text-ink-faint">
                Nothing matched “{query}”. Records you do not have access to never appear here.
              </p>
            )}

            {!loading && query.length < 2 && (
              <p className="px-3 py-4 text-small text-ink-faint">Type at least two characters.</p>
            )}

            <ul>
              {hits.map((hit, index) => (
                <li key={`${hit.type}-${hit.href}`}>
                  <button
                    type="button"
                    onMouseEnter={() => setActive(index)}
                    onClick={() => go(hit)}
                    className={cn(
                      'flex w-full items-center justify-between gap-3 rounded-card px-3 py-2 text-start',
                      index === active ? 'bg-deck' : 'hover:bg-deck',
                    )}
                  >
                    <span className="min-w-0">
                      <span className="block truncate text-h3 text-ink">{hit.label}</span>
                      {hit.subtitle && <span className="block truncate text-small text-ink-faint">{hit.subtitle}</span>}
                    </span>
                    <span className="shrink-0 rounded-pill bg-deck px-2 text-micro text-ink-faint">{hit.type}</span>
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  )
}
