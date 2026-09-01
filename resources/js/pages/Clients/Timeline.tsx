import { Head } from '@inertiajs/react'
import { PageHeader } from '@/components/shell/Page'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

interface Entry {
  id: number
  type: string
  summary: string
  body: string | null
  occurred_at: string
  user?: { name: string } | null
}

const tone: Record<string, StatusTone> = {
  call: 'info',
  whatsapp: 'success',
  email: 'info',
  meeting: 'attention',
  note: 'neutral',
  status_change: 'warning',
  system: 'neutral',
  gate: 'danger',
}

/** The full 360° history for one client, unioned across every domain. */
export default function ClientTimeline({
  record,
  activities,
}: {
  record: { id: number; full_name: string; reference: string }
  activities: Paginated<Entry>
}) {
  return (
    <>
      <Head title={`${record.full_name} — timeline`} />

      <PageHeader
        title={`${record.full_name} — timeline`}
        description={`Everything logged against ${record.reference}, newest first.`}
      />

      <Card>
        {activities.data.length === 0 ? (
          <EmptyState title="Nothing logged yet" description="Calls, messages, status changes and gate decisions land here." />
        ) : (
          <ul className="divide-y divide-line">
            {activities.data.map((entry) => (
              <li key={entry.id} className="flex gap-3 px-5 py-4">
                <StatusPill tone={tone[entry.type] ?? 'neutral'}>{entry.type.replace('_', ' ')}</StatusPill>
                <div className="min-w-0 flex-1">
                  <p className="text-h3 text-ink">{entry.summary}</p>
                  {entry.body && <p className="mt-1 text-body text-ink-soft">{entry.body}</p>}
                  <p className="mt-1 text-small text-ink-faint">
                    {entry.user?.name ?? 'System'} · <DateText value={entry.occurred_at} withTime />
                  </p>
                </div>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
