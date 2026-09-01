import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { viewingSections, type Option } from '@/pages/Brokerage/Viewings/Create'

export default function Edit({
  record,
  ...props
}: {
  record: Record<string, unknown> & { id: number }
  clients?: Option[]
  listings?: Option[]
  marinas?: Option[]
}) {
  return (
    <ResourceForm
      title="Edit viewing"
      sections={viewingSections(props)}
      initial={{
        listing_id: fv(record.listing_id),
        client_id: fv(record.client_id),
        marina_id: fv(record.marina_id),
        scheduled_at: String(fv(record.scheduled_at)).slice(0, 16),
        duration_minutes: fv(record.duration_minutes, 90),
        attendees: fv(record.attendees),
        status: fv(record.status, 'requested'),
      }}
      action={`/brokerage/viewings/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/viewings/${record.id}`}
    />
  )
}
