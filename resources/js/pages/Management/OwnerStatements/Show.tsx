import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { OwnerStatementRow } from '@/pages/Management/OwnerStatements/Index'

export default function Show({
  record,
  can,
}: {
  record: OwnerStatementRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.yacht ?? 'Statement'}
      subtitle={record.reference}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/management/owner-statements/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/management/owner-statements/${record.id}` : undefined}
      backUrl="/management/owner-statements"
      facts={[
        { label: 'Revenue', value: <Money amount={record.charter_revenue} currency={record.currency} /> },
        { label: 'Management fee', value: <Money amount={record.management_fee} currency={record.currency} /> },
        { label: 'Operating', value: <Money amount={record.operating_costs} currency={record.currency} /> },
        { label: 'Maintenance', value: <Money amount={record.maintenance_costs} currency={record.currency} /> },
        { label: 'Crew', value: <Money amount={record.crew_costs} currency={record.currency} /> },
        { label: 'Net to owner', value: <Money amount={record.net_to_owner} currency={record.currency} /> },
        { label: 'Issued', value: <DateText value={record.issued_at} withTime /> },
      ]}
    />
  )
}
