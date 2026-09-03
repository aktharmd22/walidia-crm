import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { InspectionRow } from '@/pages/Brokerage/Inspections/Index'

export default function Show({
  record,
  can,
}: {
  record: InspectionRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.yacht ?? 'Inspection'}
      subtitle={record.type.replace(/_/g, ' ')}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/inspections/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/inspections/${record.id}` : undefined}
      backUrl="/brokerage/inspections"
      facts={[
        { label: 'Inspected', value: <DateText value={record.inspected_at} withTime /> },
        { label: 'Hull', value: record.hull_condition ? `${record.hull_condition} / 5` : '—' },
        { label: 'Engines', value: record.engine_condition ? `${record.engine_condition} / 5` : '—' },
        { label: 'Interior', value: record.interior_condition ? `${record.interior_condition} / 5` : '—' },
        { label: 'Systems', value: record.systems_condition ? `${record.systems_condition} / 5` : '—' },
        { label: 'Estimated works', value: record.estimated_works_cost ? <Money amount={record.estimated_works_cost} /> : '—' },
      ]}
    />
  )
}
