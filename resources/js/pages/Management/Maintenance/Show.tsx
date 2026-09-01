import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { MaintenanceRow } from '@/pages/Management/Maintenance/Index'

export default function Show({
  record,
  can,
}: {
  record: MaintenanceRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.title}
      subtitle={record.yacht}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/management/maintenance/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/management/maintenance/${record.id}` : undefined}
      backUrl="/management/maintenance"
      facts={[
        { label: 'Category', value: record.category },
        { label: 'Priority', value: record.priority },
        { label: 'Vendor', value: record.vendor ?? '—' },
        { label: 'Due', value: <DateText value={record.due_on} /> },
        { label: 'Estimate', value: record.estimated_cost ? <Money amount={record.estimated_cost} currency={record.currency} /> : '—' },
        { label: 'Actual', value: record.actual_cost ? <Money amount={record.actual_cost} currency={record.currency} /> : 'Not settled' },
        { label: 'Blocks charter', value: record.blocks_charter ? 'Yes' : 'No' },
      ]}
    />
  )
}
