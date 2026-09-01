import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { enquirySections } from '@/pages/Charter/Enquiries/Create'

interface Option {
  value: string | number
  label: string
}

export default function EnquiryEdit({
  record,
  clients = [],
  marinas = [],
  users = [],
  experienceTypes = [],
  occasions = [],
}: {
  record: Record<string, unknown> & { id: number; reference: string }
  clients?: Option[]
  marinas?: Option[]
  users?: Option[]
  experienceTypes?: Option[]
  occasions?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.reference}`}
      sections={enquirySections(clients, marinas, users, experienceTypes, occasions)}
      initial={{
        client_id: (record.client as { id: number } | null)?.id ?? '',
        experience_type: fv(record.experience_type),
        occasion: fv(record.occasion),
        requested_date: fv(record.requested_date),
        start_time: fv(record.start_time, '10:00'),
        duration_hours: fv(record.duration_hours, 8),
        guests_adults: fv(record.guests_adults, 0),
        guests_children: fv(record.guests_children, 0),
        pickup_marina_id: (record.marina as { id: number } | null)?.id ?? '',
        dropoff_marina_id: '',
        budget_min: fv(record.budget_min),
        budget_max: fv(record.budget_max),
        currency: fv(record.currency, 'AED'),
        assigned_user_id: (record.assignee as { id: number } | null)?.id ?? '',
        itinerary_notes: fv(record.itinerary_notes),
        notes: fv(record.notes),
      }}
      action={`/charter/enquiries/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/charter/enquiries/${record.id}`}
    />
  )
}
