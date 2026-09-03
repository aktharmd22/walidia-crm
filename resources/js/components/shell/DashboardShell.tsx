import type { ReactNode } from 'react'
import { Link, usePage } from '@inertiajs/react'
import { CalendarDays, ChevronDown, ChevronRight } from 'lucide-react'
import { cn } from '@/lib/cn'
import { DropdownMenu } from '@/ui/Overlays'

/**
 * The four dashboard views share one destination.
 *
 * They answer the same question — how does the business stand — from four
 * angles, so they are tabs on one page rather than four rows in the sidebar.
 * Each keeps its own URL, so a link to the calendar still lands on the
 * calendar.
 */
const TABS = [
  { key: 'my-day', label: 'My Day', href: '/' },
  { key: 'pipeline', label: 'Pipeline', href: '/dashboard/pipeline' },
  { key: 'alerts', label: 'Alerts & Blockers', href: '/dashboard/alerts' },
  { key: 'calendar', label: 'Calendar', href: '/dashboard/calendar' },
]

export function DashboardShell({
  title,
  children,
}: {
  title: string
  children: ReactNode
}) {
  const { url } = usePage()
  const current = url.split('?')[0]

  return (
    <>
      <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-h1 text-ink">Dashboard</h1>

        {/* Where you are, in the company's own words. */}
        <nav aria-label="Breadcrumb" className="flex items-center gap-[6px] text-small">
          <span className="text-ink-faint">Walidia</span>
          <ChevronRight className="size-[14px] text-ink-faint" aria-hidden />
          <span className="text-accent-ink">{title}</span>
        </nav>
      </div>

      {/* The four views */}
      <div className="mb-5 flex flex-wrap gap-2" role="tablist" aria-label="Dashboard views">
        {TABS.map((tab) => {
          const active = current === tab.href

          return (
            <Link
              key={tab.key}
              href={tab.href}
              role="tab"
              aria-selected={active}
              className={cn(
                'rounded-pill border px-4 py-2 text-body transition-colors duration-fast ease-std',
                active
                  ? 'border-accent bg-accent text-accent-on'
                  : 'border-line bg-hull text-ink-soft hover:border-line-strong hover:text-ink',
              )}
            >
              {tab.label}
            </Link>
          )
        })}
      </div>

      {children}
    </>
  )
}

/**
 * The period control from the reference: a calendar icon, the current window,
 * a chevron. It navigates rather than filtering in place, so the figures and
 * the chart can never disagree about which months they are showing.
 */
export function PeriodMenu({
  months,
  onSelect,
}: {
  months: number
  onSelect: (months: number) => void
}) {
  const label = months === 12 ? 'This year' : `Last ${months} months`

  return (
    <DropdownMenu
      align="end"
      trigger={
        <button
          type="button"
          className="flex items-center gap-2 rounded-card border border-line bg-hull px-3 py-2 text-small text-ink-soft transition-colors duration-fast ease-std hover:border-line-strong hover:text-ink"
        >
          <CalendarDays className="size-4 text-ink-faint" aria-hidden />
          {label}
          <ChevronDown className="size-[14px] text-ink-faint" aria-hidden />
        </button>
      }
      items={[
        { key: '3', label: 'Last 3 months', onSelect: () => onSelect(3) },
        { key: '6', label: 'Last 6 months', onSelect: () => onSelect(6) },
        { key: '12', label: 'This year', onSelect: () => onSelect(12) },
      ]}
    />
  )
}
