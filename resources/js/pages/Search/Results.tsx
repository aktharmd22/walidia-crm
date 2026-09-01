import { Head, Link, router } from '@inertiajs/react'
import { useState } from 'react'
import { Search as SearchIcon } from 'lucide-react'
import { PageHeader, Toolbar } from '@/components/shell/Page'
import { Card, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface Hit {
  type: string
  label: string
  subtitle: string | null
  href: string
}

export default function Results({ query, hits = [] }: { query: string; hits?: Hit[] }) {
  const [value, setValue] = useState(query)

  const groups = hits.reduce<Record<string, Hit[]>>((accumulator, hit) => {
    accumulator[hit.type] = [...(accumulator[hit.type] ?? []), hit]
    return accumulator
  }, {})

  return (
    <>
      <Head title={query ? `Search: ${query}` : 'Search'} />

      <PageHeader
        title="Search"
        description="Clients, yachts, bookings, listings and documents you have access to."
      />

      <Toolbar
        search={value}
        onSearchChange={(next) => {
          setValue(next)
          router.get('/search', { q: next }, { preserveState: true, replace: true, only: ['hits', 'query'] })
        }}
        searchPlaceholder="Search everything…"
      />

      {hits.length === 0 ? (
        <Card>
          <EmptyState
            icon={<SearchIcon className="size-5" aria-hidden />}
            title={query.length >= 2 ? `Nothing matched “${query}”` : 'Type at least two characters'}
            description="Records outside your access never appear in results — including their existence."
          />
        </Card>
      ) : (
        Object.entries(groups).map(([type, items]) => (
          <Card key={type}>
            <div className="flex items-center gap-3 border-b border-line px-5 py-3">
              <StatusPill tone="neutral">{type}</StatusPill>
              <span className="text-small text-ink-faint">{items.length} result{items.length === 1 ? '' : 's'}</span>
            </div>
            <ul className="divide-y divide-line">
              {items.map((hit) => (
                <li key={hit.href}>
                  <Link href={hit.href} className="block px-5 py-3 hover:bg-deck">
                    <span className="block text-h3 text-ink">{hit.label}</span>
                    {hit.subtitle && <span className="block text-small text-ink-faint">{hit.subtitle}</span>}
                  </Link>
                </li>
              ))}
            </ul>
          </Card>
        ))
      )}
    </>
  )
}
