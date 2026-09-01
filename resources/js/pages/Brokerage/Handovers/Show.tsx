import { DetailShell } from '@/components/crud/DetailShell'
import { DateText } from '@/ui/Primitives'
import type { HandoverRow } from '@/pages/Brokerage/Handovers/Index'

export default function Show({
  record,
  can,
}: {
  record: HandoverRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.transaction ?? 'Handover'}
      subtitle={record.reference}
      status={record.status?.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/handovers/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/handovers/${record.id}` : undefined}
      backUrl="/brokerage/handovers"
      facts={[
          { label: 'Scheduled', value: <DateText value={record.scheduled_at} withTime /> },
          { label: 'Keys', value: record.keys_handed_over ? 'Handed over' : 'Outstanding' },
          { label: 'Documents', value: record.documents_handed_over ? 'Handed over' : 'Outstanding' },
          { label: 'Inventory', value: record.inventory_signed ? 'Signed' : 'Outstanding' },
          { label: 'Flag', value: record.flag_registration_updated ? 'Updated' : 'Outstanding' },
          { label: 'Insurance', value: record.insurance_transferred ? 'Transferred' : 'Outstanding' },
      ]}
    />
  )
}
