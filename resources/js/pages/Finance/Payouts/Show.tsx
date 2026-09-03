import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { PayoutRow } from '@/pages/Finance/Payouts/Index'

export default function Show({
  record,
  can,
}: {
  record: PayoutRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.payee_name}
      subtitle={record.reference}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/finance/payouts/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/finance/payouts/${record.id}` : undefined}
      backUrl="/finance/payouts"
      facts={[
        { label: 'Type', value: record.type.replace(/_/g, ' ') },
        { label: 'Amount', value: <Money amount={record.amount} currency={record.currency} /> },
        { label: 'Method', value: record.method.replace(/_/g, ' ') },
        { label: 'Due', value: <DateText value={record.due_on} /> },
        { label: 'Approved', value: <DateText value={record.approved_at} withTime /> },
        { label: 'Paid', value: <DateText value={record.paid_at} withTime /> },
        { label: 'Bank reference', value: <span className="numeric">{record.bank_reference ?? '—'}</span> },
      ]}
    />
  )
}
