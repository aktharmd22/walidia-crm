import { Head, Link } from '@inertiajs/react'
import { ArrowDownRight, ArrowUpRight, CalendarDays, ChevronRight, ShieldAlert } from 'lucide-react'
import { navIcon } from '@/lib/icons'
import { ChartLegend, DonutChart, ShareBar, Sparkline, TrendChart, type SeriesPoint } from '@/ui/Charts'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface Metric {
  key: string
  label: string
  value: string
  prefix?: string
  change: { value: number; direction: 'up' | 'down' } | null
  icon: string
  tone: 'accent' | 'info' | 'success' | 'warning'
  spark: number[]
  sparkVariant: 'line' | 'bar'
}

interface Charter {
  id: number
  reference: string | null
  yacht: string | null
  client: string | null
  starts_at: string
  guests: number | null
  status: string
  tone: StatusTone
  released: boolean
  url: string
}

interface Blocker {
  id: number
  title: string
  subtitle: string | null
  starts_at: string
  reasons: string[]
  url: string
}

interface TaskRow {
  id: number
  title: string
  due_at: string | null
  overdue: boolean
  priority: string
  url: string
}

interface Expiry {
  kind: string
  title: string
  subtitle: string | null
  expires_on: string | null
  expired: boolean
  blocking: boolean
  url: string | null
}

const TONE_BG: Record<Metric['tone'], string> = {
  accent: 'bg-accent-soft text-accent',
  info: 'bg-info-bg text-info',
  success: 'bg-success-bg text-success',
  warning: 'bg-warning-bg text-warning',
}

/** The four figures. Each carries its direction, because a number alone is trivia. */
function MetricCard({ metric }: { metric: Metric }) {
  const Icon = navIcon(metric.icon)

  return (
    <Card>
      <CardBody className="flex flex-col gap-4">
        <div className="flex items-start gap-3">
          <span className={`grid size-9 shrink-0 place-items-center rounded-card ${TONE_BG[metric.tone]}`}>
            <Icon className="size-[18px]" aria-hidden />
          </span>

          <span className="min-w-0 flex-1">
            <span className="block truncate text-h3 text-ink">{metric.label}</span>
            {metric.change ? (
              <span
                className={`flex items-center gap-1 text-small ${
                  metric.change.direction === 'up' ? 'text-success' : 'text-danger'
                }`}
              >
                {metric.change.direction === 'up' ? (
                  <ArrowUpRight className="size-3.5" aria-hidden />
                ) : (
                  <ArrowDownRight className="size-3.5" aria-hidden />
                )}
                <span className="numeric">{metric.change.value}%</span>
                <span className="text-ink-faint">on last month</span>
              </span>
            ) : (
              <span className="block text-small text-ink-faint">This month</span>
            )}
          </span>
        </div>

        <div className="flex items-end justify-between gap-3">
          <span className="numeric text-h1 leading-none text-ink">
            {metric.prefix && <span className="me-1 text-body text-ink-faint">{metric.prefix}</span>}
            {metric.value}
          </span>
          <Sparkline data={metric.spark} tone={metric.tone} variant={metric.sparkVariant} />
        </div>
      </CardBody>
    </Card>
  )
}

/**
 * My Day.
 *
 * Read top to bottom it answers, in order: what did we earn, where is it
 * coming from, what sails this week, what is blocked, and what expires. That
 * ordering is the whole design — a director's morning runs in that sequence,
 * and a dashboard that makes them hunt for the blocked charter is one they
 * stop opening.
 */
export default function MyDay({
  greeting,
  metrics = [],
  revenue = [],
  mix = [],
  sources = [],
  charters = [],
  blockers = [],
  tasks = [],
  expiring = [],
}: {
  greeting: string
  metrics?: Metric[]
  revenue?: SeriesPoint[]
  mix?: { name: string; value: number; tone?: 'accent' | 'info' | 'success' }[]
  sources?: { name: string; total: number; share: number }[]
  charters?: Charter[]
  blockers?: Blocker[]
  tasks?: TaskRow[]
  expiring?: Expiry[]
}) {
  const mixTotal = mix.reduce((sum, slice) => sum + slice.value, 0)
  const compact = (value: number) =>
    value >= 1_000_000 ? `${(value / 1_000_000).toFixed(1)}m` : value >= 1000 ? `${Math.round(value / 1000)}k` : String(value)

  return (
    <>
      <Head title="My Day" />

      {/* Greeting */}
      <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-h1 text-ink">{greeting}</h1>
          <p className="mt-0.5 text-body text-ink-soft">Here is where the business stands this morning.</p>
        </div>
        <p className="flex items-center gap-2 text-small text-ink-faint">
          <CalendarDays className="size-4" aria-hidden />
          <DateText value={new Date().toISOString()} />
        </p>
      </div>

      {/* What we earned */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {metrics.map((metric) => (
          <MetricCard key={metric.key} metric={metric} />
        ))}
      </div>

      {/* Where it came from */}
      <div className="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <Card>
          <CardHeader>
            <CardTitle>Revenue by month</CardTitle>
            <ChartLegend
              series={[
                { name: 'Charter', tone: 'accent' },
                { name: 'Brokerage', tone: 'info' },
                { name: 'Management', tone: 'success' },
              ]}
            />
          </CardHeader>
          <CardBody>
            {revenue.length === 0 ? (
              <EmptyState title="No revenue yet" description="Cleared payments and completed sales appear here." />
            ) : (
              <TrendChart
                data={revenue}
                series={[
                  { key: 'charter', name: 'Charter', tone: 'accent' },
                  { key: 'brokerage', name: 'Brokerage', tone: 'info' },
                  { key: 'management', name: 'Management', tone: 'success' },
                ]}
                format={compact}
              />
            )}
          </CardBody>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>This month's mix</CardTitle>
          </CardHeader>
          <CardBody className="flex flex-col gap-4">
            {mixTotal === 0 ? (
              <EmptyState title="Nothing settled yet" description="The mix appears once money clears this month." />
            ) : (
              <>
                <DonutChart data={mix} total={compact(mixTotal)} totalLabel="AED this month" />
                <ul className="flex flex-col gap-2">
                  {mix.map((slice) => (
                    <li key={slice.name} className="flex items-center justify-between gap-3 text-small">
                      <span className="flex items-center gap-2 text-ink-soft">
                        <span
                          className={`size-2 rounded-full ${
                            slice.tone === 'info' ? 'bg-info' : slice.tone === 'success' ? 'bg-success' : 'bg-accent'
                          }`}
                          aria-hidden
                        />
                        {slice.name}
                      </span>
                      <Money amount={String(slice.value)} withCurrency={false} />
                    </li>
                  ))}
                </ul>
              </>
            )}
          </CardBody>
        </Card>
      </div>

      {/* What sails, and what cannot */}
      <div className="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <Card>
          <CardHeader>
            <CardTitle>The week ahead</CardTitle>
            <Link href="/charter/bookings" className="flex items-center gap-1 text-small text-accent hover:underline">
              All charters <ChevronRight className="size-3.5" aria-hidden />
            </Link>
          </CardHeader>

          {charters.length === 0 ? (
            <EmptyState title="Nothing on the water this week" description="Confirmed charters for the next seven days appear here." />
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="bg-deck">
                    <th className="px-4 py-2.5 text-start text-micro uppercase tracking-wide text-ink-faint">Yacht</th>
                    <th className="px-4 py-2.5 text-start text-micro uppercase tracking-wide text-ink-faint">Client</th>
                    <th className="px-4 py-2.5 text-start text-micro uppercase tracking-wide text-ink-faint">Departs</th>
                    <th className="px-4 py-2.5 text-end text-micro uppercase tracking-wide text-ink-faint">Guests</th>
                    <th className="px-4 py-2.5 text-start text-micro uppercase tracking-wide text-ink-faint">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {charters.map((charter) => (
                    <tr key={charter.id} className="border-b border-line last:border-0 hover:bg-deck">
                      <td className="h-row px-4">
                        <Link href={charter.url} className="text-h3 text-ink hover:text-accent">
                          {charter.yacht ?? 'Yacht'}
                        </Link>
                        <span className="block text-micro text-ink-faint">{charter.reference}</span>
                      </td>
                      <td className="h-row px-4 text-body text-ink-soft">{charter.client ?? '—'}</td>
                      <td className="h-row px-4">
                        <DateText value={charter.starts_at} withTime className="text-small text-ink-soft" />
                      </td>
                      <td className="h-row px-4 text-end">
                        {charter.guests ? <Num value={charter.guests} /> : <span className="text-ink-faint">—</span>}
                      </td>
                      <td className="h-row px-4">
                        <span className="flex items-center gap-2">
                          <StatusPill tone={charter.tone}>{charter.status.replace(/_/g, ' ')}</StatusPill>
                          {!charter.released && <StatusPill tone="warning">Not released</StatusPill>}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Where the work comes from</CardTitle>
          </CardHeader>

          {sources.length === 0 ? (
            <EmptyState title="No leads yet" description="Lead sources appear here as enquiries arrive." />
          ) : (
            <ul className="flex flex-col gap-4 p-5">
              {sources.map((source) => (
                <li key={source.name} className="flex flex-col gap-1.5">
                  <span className="flex items-center justify-between gap-3">
                    <span className="truncate text-body text-ink">{source.name}</span>
                    <Num value={source.total} className="shrink-0 text-small text-ink-soft" />
                  </span>
                  <ShareBar value={source.share} />
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>

      {/* What is stuck */}
      <div className="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Blocked</CardTitle>
            <Link href="/dashboard/alerts" className="flex items-center gap-1 text-small text-accent hover:underline">
              All alerts <ChevronRight className="size-3.5" aria-hidden />
            </Link>
          </CardHeader>

          {blockers.length === 0 ? (
            <EmptyState
              icon={<ShieldAlert className="size-5" aria-hidden />}
              title="Nothing blocked"
              description="Every upcoming charter has cleared its gates."
            />
          ) : (
            <ul className="divide-y divide-line">
              {blockers.map((blocker) => (
                <li key={blocker.id} className="px-5 py-3">
                  <Link href={blocker.url} className="block text-h3 text-ink hover:text-accent">
                    {blocker.title}
                  </Link>
                  <span className="block text-micro text-ink-faint">
                    {blocker.subtitle} · <DateText value={blocker.starts_at} />
                  </span>
                  {/* The gate's own words, so this list and the button agree. */}
                  <ul className="mt-1.5 flex flex-col gap-1">
                    {blocker.reasons.map((reason) => (
                      <li key={reason} className="text-small text-danger">
                        {reason}
                      </li>
                    ))}
                  </ul>
                </li>
              ))}
            </ul>
          )}
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Your tasks</CardTitle>
            <Link href="/tasks" className="flex items-center gap-1 text-small text-accent hover:underline">
              All tasks <ChevronRight className="size-3.5" aria-hidden />
            </Link>
          </CardHeader>

          {tasks.length === 0 ? (
            <EmptyState title="Nothing outstanding" description="Tasks assigned to you appear here." />
          ) : (
            <ul className="divide-y divide-line">
              {tasks.map((task) => (
                <li key={task.id} className="flex items-center justify-between gap-3 px-5 py-3">
                  <Link href={task.url} className="min-w-0 flex-1 truncate text-body text-ink hover:text-accent">
                    {task.title}
                  </Link>
                  {task.due_at ? (
                    <DateText
                      value={task.due_at}
                      className={`shrink-0 text-small ${task.overdue ? 'text-danger' : 'text-ink-faint'}`}
                    />
                  ) : (
                    <span className="shrink-0 text-small text-ink-faint">No date</span>
                  )}
                </li>
              ))}
            </ul>
          )}
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Expiring soon</CardTitle>
            <Link href="/management/certificates/expiry" className="flex items-center gap-1 text-small text-accent hover:underline">
              Compliance <ChevronRight className="size-3.5" aria-hidden />
            </Link>
          </CardHeader>

          {expiring.length === 0 ? (
            <EmptyState title="Nothing expiring" description="Certificates, crew documents and mandates are all in date." />
          ) : (
            <ul className="divide-y divide-line">
              {expiring.map((item) => (
                <li key={`${item.kind}-${item.title}-${item.expires_on}`} className="flex items-center gap-3 px-5 py-3">
                  <span className="min-w-0 flex-1">
                    {item.url ? (
                      <Link href={item.url} className="block truncate text-body text-ink hover:text-accent">
                        {item.title}
                      </Link>
                    ) : (
                      <span className="block truncate text-body text-ink">{item.title}</span>
                    )}
                    <span className="block truncate text-micro text-ink-faint">
                      {item.kind}
                      {item.subtitle ? ` · ${item.subtitle}` : ''}
                    </span>
                  </span>
                  <span className="flex shrink-0 items-center gap-2">
                    <DateText value={item.expires_on} className="text-small text-ink-soft" />
                    <StatusPill tone={item.expired ? 'danger' : item.blocking ? 'warning' : 'neutral'}>
                      {item.expired ? 'Expired' : 'Soon'}
                    </StatusPill>
                  </span>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>
    </>
  )
}
