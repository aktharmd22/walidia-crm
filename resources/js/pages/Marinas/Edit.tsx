import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { marinaSections } from '@/pages/Marinas/Create'

export default function MarinaEdit({
  record,
}: {
  record: Record<string, unknown> & { id: number; name: string }
}) {
  return (
    <ResourceForm
      title={`Edit ${record.name}`}
      sections={marinaSections()}
      initial={{
        name: fv(record.name),
        name_ar: fv(record.name_ar),
        country: fv(record.country),
        emirate: fv(record.emirate),
        city: fv(record.city),
        timezone: fv(record.timezone, 'Asia/Dubai'),
        latitude: fv(record.latitude),
        longitude: fv(record.longitude),
        contact_name: fv(record.contact_name),
        contact_phone: fv(record.contact_phone),
        contact_email: fv(record.contact_email),
        requires_manifest: Boolean(record.requires_manifest),
        manifest_format: fv(record.manifest_format),
        is_active: Boolean(record.is_active),
        notes: fv(record.notes),
      }}
      action={`/fleet/marinas/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/fleet/marinas/${record.id}`}
    />
  )
}
