import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { ListingRow } from '@/pages/Brokerage/Listings/Index'

export default function Show({
  record,
  can,
}: {
  record: ListingRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.yacht ?? 'Listing'}
      subtitle={record.reference}
      status={record.status?.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/listings/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/listings/${record.id}` : undefined}
      backUrl="/brokerage/listings"
      facts={[
          { label: 'Mandate', value: record.mandate_type.replace(/_/g, ' ') },
          { label: 'Asking', value: <Money amount={record.asking_price} currency={record.currency} /> },
          { label: 'Commission', value: <span className="numeric">{record.commission_rate}%</span> },
          { label: 'Mandate ends', value: <DateText value={record.agreement_expires_on} /> },
          { label: 'Broker', value: record.assignee ?? '—' },
          { label: 'Published', value: record.is_published ? 'Yes' : 'No' },
      ]}
    />
  )
}
