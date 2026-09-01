import { DetailShell } from '@/components/crud/DetailShell'
import { Money } from '@/ui/Primitives'
import type { BuyerRequirementRow } from '@/pages/Brokerage/BuyerRequirements/Index'

export default function Show({
  record,
  can,
}: {
  record: BuyerRequirementRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.client ?? 'Requirement'}
      subtitle={record.reference}
      status={record.status?.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/buyer-requirements/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/buyer-requirements/${record.id}` : undefined}
      backUrl="/brokerage/buyer-requirements"
      facts={[
          { label: 'Budget', value: record.budget_max ? <Money amount={record.budget_max} currency={record.currency} /> : 'Open' },
          { label: 'Length', value: <span className="numeric">{record.loa_min ?? '—'}–{record.loa_max ?? '—'} m</span> },
          { label: 'Built from', value: <span className="numeric">{record.year_from ?? '—'}</span> },
          { label: 'Use', value: record.use_case ?? '—' },
      ]}
    />
  )
}
