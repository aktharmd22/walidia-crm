import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { vendorSections } from '@/pages/Vendors/Create'

interface Option {
  value: string | number
  label: string
}

export default function VendorEdit({
  record,
  categories = [],
}: {
  record: Record<string, unknown> & { id: number; display_name: string }
  categories?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.display_name}`}
      sections={vendorSections(categories)}
      initial={{
        legal_name: fv(record.legal_name),
        trade_name: fv(record.trade_name),
        vendor_category_id: fv(record.vendor_category_id),
        status: fv(record.status, 'active'),
        trn: fv(record.trn),
        trade_licence_no: fv(record.trade_licence_no),
        licence_expiry: fv(record.licence_expiry),
        payment_terms_days: fv(record.payment_terms_days, 30),
        contact_name: fv(record.contact_name),
        email: fv(record.email),
        mobile: fv(record.mobile),
        notes: fv(record.notes),
      }}
      action={`/vendors/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/vendors/${record.id}`}
    />
  )
}
