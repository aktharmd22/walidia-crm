import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { companySections } from '@/pages/Companies/Create'

interface Option {
  value: string | number
  label: string
}

export default function CompanyEdit({
  record,
  users = [],
  types = [],
}: {
  record: Record<string, unknown> & { id: number; display_name: string }
  users?: Option[]
  types?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.display_name}`}
      sections={companySections(users, types)}
      initial={{
        legal_name: fv(record.legal_name),
        trade_name: fv(record.trade_name),
        type: fv(record.type, 'corporate'),
        status: fv(record.status, 'active'),
        email: fv(record.email),
        phone: fv(record.phone),
        website: fv(record.website),
        assigned_user_id: fv(record.assigned_user_id),
        trn: fv(record.trn),
        trade_licence_no: fv(record.trade_licence_no),
        licence_expiry: fv(record.licence_expiry),
        billing_email: fv(record.billing_email),
        payment_terms_days: fv(record.payment_terms_days, 0),
        commission_rate_default: fv(record.commission_rate_default),
        address_line1: fv(record.address_line1),
        city: fv(record.city),
        emirate: fv(record.emirate),
        country: fv(record.country),
        notes: fv(record.notes),
      }}
      action={`/companies/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/companies/${record.id}`}
    />
  )
}
