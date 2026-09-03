import { Link } from '@inertiajs/react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { DateText, Money, Num } from '@/ui/Primitives'
import type { DealRow } from '@/pages/Deals/Index'

export default function DealShow({
  record,
  timeline = [],
  can,
}: {
  record: DealRow
  timeline?: TimelineEntry[]
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.title}
      subtitle={record.reference}
      status={record.stage?.name ?? record.status}
      statusTone={record.stage?.tone ?? 'neutral'}
      editUrl={can.update ? `/deals/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/deals/${record.id}` : undefined}
      backUrl="/deals"
      facts={[
        {
          label: 'Value',
          value:
            record.value !== undefined && record.value !== null ? (
              <Money amount={record.value} currency={record.currency} />
            ) : (
              'Restricted'
            ),
        },
        { label: 'Pipeline', value: record.pipeline?.name ?? '—' },
        {
          label: 'Client',
          value: record.client ? (
            <Link href={`/clients/${record.client.id}`} className="text-accent-ink hover:underline">
              {record.client.name}
            </Link>
          ) : (
            '—'
          ),
        },
        { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
        { label: 'Days in stage', value: <Num value={record.days_in_stage} /> },
        { label: 'Expected close', value: <DateText value={record.expected_close_date} /> },
        { label: 'Status', value: record.status },
      ]}
      timeline={timeline}
    />
  )
}
