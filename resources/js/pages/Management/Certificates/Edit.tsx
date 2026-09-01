import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Management/Certificates/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        type: fv(record.type, 'safety'),
        name: fv(record.name),
        number: fv(record.number),
        issued_by: fv(record.issued_by),
        issued_on: fv(record.issued_on),
        expires_on: fv(record.expires_on),
        blocks_charter: fv(record.blocks_charter, true),
        status: fv(record.status, 'valid'),
        notes: fv(record.notes),
      }}
      action={`/management/certificates/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/management/certificates/${record.id}`}
    />
  )
}

export type { Option }
