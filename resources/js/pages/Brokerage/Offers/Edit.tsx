import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { offerSections, type Option } from '@/pages/Brokerage/Offers/Create'

export default function Edit({
  record,
  ...props
}: {
  record: Record<string, unknown> & { id: number }
  clients?: Option[]
  listings?: Option[]
}) {
  return (
    <ResourceForm
      title="Edit offer"
      sections={offerSections(props)}
      initial={{
        listing_id: fv(record.listing_id),
        client_id: fv(record.client_id),
        amount: fv(record.amount),
        currency: fv(record.currency, 'EUR'),
        deposit_amount: fv(record.deposit_amount),
        valid_until: fv(record.valid_until),
        subject_to_survey: fv(record.subject_to_survey, true),
        subject_to_sea_trial: fv(record.subject_to_sea_trial, true),
        proof_of_funds_received: fv(record.proof_of_funds_received, false),
        conditions: fv(record.conditions),
        status: fv(record.status, 'draft'),
      }}
      action={`/brokerage/offers/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/offers/${record.id}`}
    />
  )
}
