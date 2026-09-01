import { Head, Link, router } from '@inertiajs/react'
import { CalendarClock } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface Row {
  id: number
  crew: string | null
  crew_id: number
  role: string | null
  type: string
  expires_on: string | null
  is_expired: boolean
  tone: StatusTone
}

/**
 * The screen that stops a charter being held at the marina gate: every crew
 * document expiring, worst first.
 */
export default function CrewExpiry({ rows = [], days }: { rows?: Row[]; days: number }) {
  return (
    <>
      <Head title="Crew document expiry" />

      <PageHeader
        title="Crew document expiry"
        description={`Documents expiring within ${days} days, and anything already expired.`}
        actions={
          <div className="flex gap-2">
            {[30, 60, 90].map((window) => (
              <Button
                key={window}
                size="sm"
                variant={days === window ? 'primary' : 'secondary'}
                onClick={() => router.get('/crew/expiry', { days: window })}
              >
                {window} days
              </Button>
            ))}
          </div>
        }
      />

      <Card>
        {rows.length === 0 ? (
          <EmptyState
            icon={<CalendarClock className="size-5" aria-hidden />}
            title="Nothing expiring"
            description="Every crew document on file is valid beyond this window."
          />
        ) : (
          <ul className="divide-y divide-line">
            {rows.map((row) => (
              <li key={row.id} className="flex flex-wrap items-center gap-3 px-5 py-3">
                <Link href={`/crew/${row.crew_id}`} className="min-w-0 flex-1">
                  <span className="block text-h3 text-ink">{row.crew}</span>
                  <span className="block text-small text-ink-faint">
                    {row.role} · {row.type.replace(/_/g, ' ')}
                  </span>
                </Link>
                <DateText value={row.expires_on} className="text-small text-ink-soft" />
                <StatusPill tone={row.tone}>{row.is_expired ? 'Expired' : 'Expiring'}</StatusPill>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
