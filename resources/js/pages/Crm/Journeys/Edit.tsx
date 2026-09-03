import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { journeySections, type Option } from '@/pages/Crm/Journeys/Create'

export default function Edit({
  record,
  ...props
}: {
  record: Record<string, unknown> & { id: number }
  clients?: Option[]
}) {
  return (
    <ResourceForm
      title="Edit journey"
      sections={journeySections(props)}
      initial={{
        client_id: fv(record.client_id),
        type: fv(record.type, 'post_charter'),
        status: fv(record.status, 'open'),
      }}
      action={`/crm/journeys/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/crm/journeys/${record.id}`}
    />
  )
}
