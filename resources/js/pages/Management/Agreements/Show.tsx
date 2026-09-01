import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { AgreementRow } from '@/pages/Management/Agreements/Index'

export default function Show({
  record,
  can,
}: {
  record: AgreementRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.yacht ?? 'Agreement'}
      subtitle={record.reference}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/management/agreements/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/management/agreements/${record.id}` : undefined}
      backUrl="/management/agreements"
      facts={[
        { label: 'Scope', value: record.scope.replace(/_/g, ' ') },
        { label: 'Fee model', value: record.fee_model },
        { label: 'Monthly fee', value: record.monthly_fee ? <Money amount={record.monthly_fee} currency={record.currency} /> : 'Restricted' },
        { label: 'Starts', value: <DateText value={record.starts_on} /> },
        { label: 'Ends', value: <DateText value={record.ends_on} /> },
        { label: 'Notice', value: <span className="numeric">{record.notice_days} days</span> },
      ]}
    />
  )
}
