import { Head, Link } from '@inertiajs/react'
import { AlertTriangle, CalendarDays, CheckSquare, Ship, TrendingUp, Wallet } from 'lucide-react'
import { MetricCard, PageHeader } from '@/components/shell/Page'
import { Card, CardHeader, CardTitle, DateText, EmptyState, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Button } from '@/ui/Button'
import type { StatusTone } from '@/types'

interface Metric {
  key: string
  label: string
  value: string
  icon: 'ship' | 'wallet' | 'trend' | 'calendar'
  tone: StatusTone
  delta?: string
  comparison?: string
  href?: string
}

interface Blocker {
  id: string
  severity: 'hard' | 'soft'
  subject: string
  message: string
  href: string
}

interface TaskRow {
  id: number
  title: string
  due_at: string | null
  subject: string | null
  overdue: boolean
  href: string
}

interface UpcomingRow {
  id: number
  reference: string
  yacht: string
  client: string
  starts_at: string
  status: string
  status_tone: StatusTone
  value: string | null
  href: string
}

const icons = {
  ship: Ship,
  wallet: Wallet,
  trend: TrendingUp,
  calendar: CalendarDays,
}

export default function MyDay({
  metrics = [],
  blockers = [],
  tasks = [],
  upcoming = [],
}: {
  metrics?: Metric[]
  blockers?: Blocker[]
  tasks?: TaskRow[]
  upcoming?: UpcomingRow[]
}) {
  return (
    <>
      <Head title="My Day" />

      <PageHeader
        title="My Day"
        description="What needs you today: blocked transitions first, then tasks, then what is on the water."
        actions={
          <Link href="/charter/enquiries/create">
            <Button variant="primary">+ New enquiry</Button>
          </Link>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {metrics.map((metric) => {
          const Icon = icons[metric.icon]
          return (
            <MetricCard
              key={metric.key}
              label={metric.label}
              value={metric.value}
              icon={<Icon className="size-4" aria-hidden />}
              tone={metric.tone}
              delta={metric.delta}
              comparison={metric.comparison}
              href={metric.href}
            />
          )
        })}
      </div>

      <div className="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <Card>
          <CardHeader>
            <CardTitle>Upcoming charters</CardTitle>
            <Link href="/charter/calendar" className="text-small text-accent hover:underline">
              Open calendar
            </Link>
          </CardHeader>
          {upcoming.length === 0 ? (
            <EmptyState
              icon={<Ship className="size-5" aria-hidden />}
              title="Nothing on the water yet"
              description="Confirmed bookings for the next 14 days appear here, with their operational release state."
            />
          ) : (
            <ul className="divide-y divide-line">
              {upcoming.map((row) => (
                <li key={row.id}>
                  <Link href={row.href} className="flex items-center gap-4 px-5 py-3 hover:bg-deck">
                    <span className="min-w-0 flex-1">
                      <span className="block truncate text-h3 text-ink">{row.yacht}</span>
                      <span className="block truncate text-small text-ink-faint">
                        {row.client} · {row.reference}
                      </span>
                    </span>
                    <StatusPill tone={row.status_tone}>{row.status}</StatusPill>
                    <DateText value={row.starts_at} className="hidden text-small text-ink-soft sm:block" />
                    {row.value && <Money amount={row.value} className="hidden text-body text-ink md:block" />}
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </Card>

        <div className="flex flex-col gap-5">
          <Card>
            <CardHeader>
              <CardTitle>Alerts &amp; blockers</CardTitle>
              <Link href="/dashboard/alerts" className="text-small text-accent hover:underline">
                All alerts
              </Link>
            </CardHeader>
            {blockers.length === 0 ? (
              <EmptyState
                icon={<AlertTriangle className="size-5" aria-hidden />}
                title="Nothing is blocked"
                description="Hard gates that stop a transition and soft warnings both surface here."
              />
            ) : (
              <ul className="divide-y divide-line">
                {blockers.map((blocker) => (
                  <li key={blocker.id} className="px-5 py-3">
                    <Link href={blocker.href} className="flex items-start gap-3">
                      <StatusPill tone={blocker.severity === 'hard' ? 'danger' : 'warning'}>
                        {blocker.severity === 'hard' ? 'Blocked' : 'Warning'}
                      </StatusPill>
                      <span className="min-w-0">
                        <span className="block text-h3 text-ink">{blocker.subject}</span>
                        <span className="block text-small text-ink-soft">{blocker.message}</span>
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>My tasks</CardTitle>
              <Link href="/tasks" className="text-small text-accent hover:underline">
                All tasks
              </Link>
            </CardHeader>
            {tasks.length === 0 ? (
              <EmptyState
                icon={<CheckSquare className="size-5" aria-hidden />}
                title="No open tasks"
                description="Next actions from the pipeline, and anything a gate or workflow raised for you."
              />
            ) : (
              <ul className="divide-y divide-line">
                {tasks.map((task) => (
                  <li key={task.id}>
                    <Link href={task.href} className="flex items-center gap-3 px-5 py-3 hover:bg-deck">
                      <span className="min-w-0 flex-1">
                        <span className="block truncate text-h3 text-ink">{task.title}</span>
                        {task.subject && (
                          <span className="block truncate text-small text-ink-faint">{task.subject}</span>
                        )}
                      </span>
                      {task.due_at && (
                        <StatusPill tone={task.overdue ? 'danger' : 'neutral'}>
                          <DateText value={task.due_at} />
                        </StatusPill>
                      )}
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </Card>
        </div>
      </div>
    </>
  )
}
