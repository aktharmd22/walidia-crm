import type { ReactNode } from 'react'
import { Link } from '@inertiajs/react'
import { ChevronLeft, ChevronRight, Filter, MoreHorizontal, Search } from 'lucide-react'
import { cn } from '@/lib/cn'
import { Button } from '@/ui/Button'
import { DropdownMenu, type MenuItem } from '@/ui/Overlays'
import { Num } from '@/ui/Primitives'
import type { Paginated, StatusTone } from '@/types'

/* ── page header: title + primary action ────────────────────────────────── */

export function PageHeader({
  title,
  description,
  actions,
  className,
}: {
  title: string
  description?: string
  actions?: ReactNode
  className?: string
}) {
  return (
    <div className={cn('flex flex-wrap items-start justify-between gap-4', className)}>
      <div className="min-w-0">
        <h1 className="text-h1 text-ink">{title}</h1>
        {description && <p className="mt-1 max-w-prose text-body text-ink-soft">{description}</p>}
      </div>
      {actions && <div className="flex flex-wrap items-center gap-3">{actions}</div>}
    </div>
  )
}

/* ── toolbar: search + filters + import/export + CTA ────────────────────── */

export function Toolbar({
  search,
  onSearchChange,
  searchPlaceholder = 'Search…',
  onFilter,
  filterCount = 0,
  exportHref,
  importHref,
  children,
}: {
  search?: string
  onSearchChange?: (value: string) => void
  searchPlaceholder?: string
  onFilter?: () => void
  filterCount?: number
  exportHref?: string
  importHref?: string
  children?: ReactNode
}) {
  return (
    <div className="flex flex-wrap items-center gap-3">
      {onSearchChange && (
        <div className="relative min-w-[220px] flex-1 md:max-w-[420px]">
          <Search className="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-ink-faint" aria-hidden />
          <input
            type="search"
            value={search ?? ''}
            onChange={(event) => onSearchChange(event.target.value)}
            placeholder={searchPlaceholder}
            aria-label={searchPlaceholder}
            className="h-field w-full rounded-card border border-line bg-hull ps-[34px] pe-3 text-body text-ink placeholder:text-ink-faint hover:border-line-strong"
          />
        </div>
      )}

      <div className="flex flex-wrap items-center gap-3 ms-auto">
        {onFilter && (
          <Button variant="secondary" icon={<Filter className="size-4" />} onClick={onFilter}>
            Filter
            {filterCount > 0 && (
              <span className="numeric ms-1 rounded-pill bg-accent-soft px-2 text-micro text-accent-ink">
                {filterCount}
              </span>
            )}
          </Button>
        )}
        {exportHref && (
          <Button variant="secondary" onClick={() => (window.location.href = exportHref)}>
            Export CSV
          </Button>
        )}
        {importHref && (
          <Link href={importHref}>
            <Button variant="secondary">Import CSV</Button>
          </Link>
        )}
        {children}
      </div>
    </div>
  )
}

/* ── metric card ────────────────────────────────────────────────────────── */

/* Written out in full: Tailwind cannot see an interpolated class name. */
const deltaTextClasses: Record<StatusTone, string> = {
  success: 'text-success',
  info: 'text-info',
  warning: 'text-warning',
  attention: 'text-attention',
  danger: 'text-danger',
  neutral: 'text-neutral',
}

const toneClasses: Record<StatusTone, string> = {
  success: 'bg-success-bg text-success',
  info: 'bg-info-bg text-info',
  warning: 'bg-warning-bg text-warning',
  attention: 'bg-attention-bg text-attention',
  danger: 'bg-danger-bg text-danger',
  neutral: 'bg-neutral-bg text-neutral',
}

export function MetricCard({
  label,
  value,
  icon,
  tone = 'info',
  delta,
  deltaTone = 'success',
  comparison,
  menu,
  href,
}: {
  label: string
  value: ReactNode
  icon?: ReactNode
  tone?: StatusTone
  delta?: string
  deltaTone?: StatusTone
  comparison?: string
  menu?: MenuItem[]
  href?: string
}) {
  const body = (
    <div className="flex flex-col gap-3 rounded-card border border-line bg-hull p-5">
      <div className="flex items-start justify-between gap-3">
        <span className="flex items-center gap-3 text-h3 text-ink-soft">
          {icon && <span className={cn('grid size-8 place-items-center rounded-card', toneClasses[tone])}>{icon}</span>}
          {label}
        </span>
        {menu && (
          <DropdownMenu
            items={menu}
            trigger={
              <button type="button" className="rounded-pill p-1 text-ink-faint hover:bg-deck" aria-label={`${label} options`}>
                <MoreHorizontal className="size-4" aria-hidden />
              </button>
            }
          />
        )}
      </div>
      <span className="text-display text-ink numeric">{value}</span>
      {(delta || comparison) && (
        <span className="flex items-center gap-2 text-small">
          {delta && <span className={cn('numeric font-medium', deltaTextClasses[deltaTone])}>{delta}</span>}
          {comparison && <span className="text-ink-faint">{comparison}</span>}
        </span>
      )}
    </div>
  )

  return href ? (
    <Link href={href} className="block transition-colors duration-fast hover:border-line-strong">
      {body}
    </Link>
  ) : (
    body
  )
}

/* ── pagination ─────────────────────────────────────────────────────────── */

export function Pagination<T>({ page, onNavigate }: { page: Paginated<T>; onNavigate: (url: string) => void }) {
  const { meta, links } = page
  if (meta.last_page <= 1) return null

  const previous = links[0]
  const next = links[links.length - 1]
  const numbers = links.slice(1, -1)

  return (
    <nav className="flex flex-wrap items-center justify-between gap-3 py-4" aria-label="Pagination">
      <p className="text-small text-ink-faint">
        Showing <Num value={meta.from ?? 0} /> to <Num value={meta.to ?? 0} /> of <Num value={meta.total} />
      </p>

      <div className="flex items-center gap-2">
        <Button
          variant="secondary"
          size="sm"
          disabled={!previous?.url}
          onClick={() => previous?.url && onNavigate(previous.url)}
          icon={<ChevronLeft className="size-4" />}
        >
          Previous
        </Button>

        <div className="hidden items-center gap-1 sm:flex">
          {numbers.map((link, index) => (
            <button
              key={`${link.label}-${index}`}
              type="button"
              disabled={!link.url}
              onClick={() => link.url && onNavigate(link.url)}
              aria-current={link.active ? 'page' : undefined}
              className={cn(
                'numeric h-8 min-w-8 rounded-pill px-2 text-small',
                link.active
                  ? 'bg-accent text-accent-on'
                  : 'text-ink-soft hover:bg-deck disabled:text-ink-faint disabled:hover:bg-transparent',
              )}
            >
              {link.label}
            </button>
          ))}
        </div>

        <Button
          variant="secondary"
          size="sm"
          disabled={!next?.url}
          onClick={() => next?.url && onNavigate(next.url)}
          iconEnd={<ChevronRight className="size-4" />}
        >
          Next
        </Button>
      </div>
    </nav>
  )
}

/* ── bulk action bar ────────────────────────────────────────────────────── */

export function BulkBar({
  count,
  onClear,
  children,
}: {
  count: number
  onClear: () => void
  children?: ReactNode
}) {
  if (count === 0) return null

  return (
    <div className="flex flex-wrap items-center gap-3 rounded-card border border-accent bg-accent-soft px-4 py-3">
      <span className="text-h3 text-ink">
        <Num value={count} /> selected
      </span>
      <div className="flex flex-wrap items-center gap-2 ms-auto">
        {children}
        <Button variant="ghost" size="sm" onClick={onClear}>
          Clear
        </Button>
      </div>
    </div>
  )
}
