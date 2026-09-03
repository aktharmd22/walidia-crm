import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { ValuationRow } from '@/pages/Brokerage/Valuations/Index'

export default function Show({
  record,
  can,
}: {
  record: ValuationRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.yacht ?? 'Valuation'}
      subtitle={record.reference}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/valuations/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/valuations/${record.id}` : undefined}
      backUrl="/brokerage/valuations"
      facts={[
        { label: 'Valued on', value: <DateText value={record.valued_on} /> },
        { label: 'Broker valuation', value: <Money amount={record.broker_valuation} currency={record.currency} /> },
        { label: 'Market low', value: record.market_low ? <Money amount={record.market_low} currency={record.currency} /> : '—' },
        { label: 'Market high', value: record.market_high ? <Money amount={record.market_high} currency={record.currency} /> : '—' },
        { label: 'Recommended', value: record.recommended_asking ? <Money amount={record.recommended_asking} currency={record.currency} /> : '—' },
        { label: 'Agreed asking', value: record.agreed_asking ? <Money amount={record.agreed_asking} currency={record.currency} /> : 'Not decided' },
      ]}
    />
  )
}
