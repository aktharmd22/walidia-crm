import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Listings/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[]; owners?: Option[]; users?: Option[] }) {
  return (
    <ResourceForm
      title="Edit listing"
      sections={sections(props as { yachts?: Option[]; owners?: Option[]; users?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        yacht_owner_id: fv(record.yacht_owner_id),
        mandate_type: fv(record.mandate_type, 'central'),
        asking_price: fv(record.asking_price),
        currency: fv(record.currency, 'EUR'),
        reserve_price: fv(record.reserve_price),
        commission_rate: fv(record.commission_rate, 5),
        agreement_signed_on: fv(record.agreement_signed_on),
        agreement_expires_on: fv(record.agreement_expires_on),
        assigned_user_id: fv(record.assigned_user_id),
        status: fv(record.status, 'draft'),
        requires_nda: fv(record.requires_nda, true),
        requires_proof_of_funds: fv(record.requires_proof_of_funds, true),
        is_published: fv(record.is_published, false),
        marketing_summary: fv(record.marketing_summary),
        notes: fv(record.notes),
      }}
      action={`/brokerage/listings/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/listings/${record.id}`}
    />
  )
}

export type { Option }
