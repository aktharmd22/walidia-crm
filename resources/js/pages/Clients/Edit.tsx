import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { clientSections } from '@/pages/Clients/Create'

interface Option {
  value: string | number
  label: string
}

export default function ClientEdit({
  record,
  companies = [],
  sources = [],
  users = [],
  canEditVip = false,
}: {
  record: Record<string, unknown> & { id: number; full_name: string }
  companies?: Option[]
  sources?: Option[]
  users?: Option[]
  canEditVip?: boolean
}) {
  return (
    <ResourceForm
      title={`Edit ${record.full_name}`}
      description="Changes are audited: who changed what, from what, and when."
      sections={clientSections(companies, sources, users, canEditVip)}
      initial={{
        salutation: fv(record.salutation),
        first_name: fv(record.first_name),
        last_name: fv(record.last_name),
        full_name_ar: fv(record.full_name_ar),
        client_type: fv(record.client_type, []),
        company_id: (record.company as { id: number } | null)?.id ?? '',
        position: fv(record.position),
        email: fv(record.email),
        mobile: fv(record.mobile),
        phone_alt: fv(record.phone_alt),
        preferred_channel: fv(record.preferred_channel, 'whatsapp'),
        nationality: fv(record.nationality),
        country: fv(record.country),
        city: fv(record.city),
        emirate: fv(record.emirate),
        address_line1: fv(record.address_line1),
        vip_level: fv(record.vip_level, 'none'),
        status: fv(record.status, 'active'),
        assigned_user_id: (record.assignee as { id: number } | null)?.id ?? '',
        source_id: fv(record.source_id),
        notes: fv(record.notes),
        passport_number: fv(record.passport_number),
        passport_expiry: fv(record.passport_expiry),
        emirates_id: fv(record.emirates_id),
        date_of_birth: fv(record.date_of_birth),
        dietary_preferences: fv(record.dietary_preferences),
        allergies: fv(record.allergies),
        notes_vip: fv(record.notes_vip),
      }}
      action={`/clients/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/clients/${record.id}`}
    />
  )
}
