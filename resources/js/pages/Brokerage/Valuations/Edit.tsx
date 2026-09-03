import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Valuations/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[]; listings?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        listing_id: fv(record.listing_id),
        valued_on: fv(record.valued_on),
        market_low: fv(record.market_low),
        market_high: fv(record.market_high),
        broker_valuation: fv(record.broker_valuation),
        recommended_asking: fv(record.recommended_asking),
        currency: fv(record.currency, 'EUR'),
        status: fv(record.status, 'draft'),
        rationale: fv(record.rationale),
      }}
      action={`/brokerage/valuations/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/valuations/${record.id}`}
    />
  )
}

export type { Option }
