import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

const currencies = ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'OMR'].map((code) => ({ value: code, label: code }))

export function enquirySections(
  clients: Option[],
  marinas: Option[],
  users: Option[],
  experienceTypes: Option[],
  occasions: Option[],
): FormSection[] {
  return [
    {
      title: 'The enquiry',
      fields: [
        { name: 'client_id', label: 'Client', type: 'select', required: true, options: clients },
        { name: 'experience_type', label: 'Experience', type: 'select', options: experienceTypes },
        { name: 'occasion', label: 'Occasion', type: 'select', options: occasions },
        { name: 'requested_date', label: 'Requested date', type: 'date' },
        { name: 'start_time', label: 'Start time', placeholder: '10:00' },
        { name: 'duration_hours', label: 'Duration (hours)', type: 'number' },
        { name: 'guests_adults', label: 'Adults', type: 'number', required: true },
        { name: 'guests_children', label: 'Children', type: 'number', required: true },
        { name: 'pickup_marina_id', label: 'Departure marina', type: 'select', options: marinas },
        { name: 'dropoff_marina_id', label: 'Return marina', type: 'select', options: marinas },
        { name: 'budget_min', label: 'Budget from', type: 'money' },
        { name: 'budget_max', label: 'Budget to', type: 'money' },
        { name: 'currency', label: 'Currency', type: 'select', required: true, options: currencies },
        { name: 'assigned_user_id', label: 'Owner', type: 'select', options: users },
        { name: 'itinerary_notes', label: 'Itinerary notes', type: 'textarea', wide: true },
        { name: 'notes', label: 'Internal notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function EnquiryCreate({
  clients = [],
  marinas = [],
  users = [],
  experienceTypes = [],
  occasions = [],
}: {
  clients?: Option[]
  marinas?: Option[]
  users?: Option[]
  experienceTypes?: Option[]
  occasions?: Option[]
}) {
  return (
    <ResourceForm
      title="New charter enquiry"
      description="What the client asked for. Matching, pricing and the booking all read this."
      sections={enquirySections(clients, marinas, users, experienceTypes, occasions)}
      initial={{
        client_id: '',
        experience_type: '',
        occasion: '',
        requested_date: '',
        start_time: '10:00',
        duration_hours: 8,
        guests_adults: 8,
        guests_children: 0,
        pickup_marina_id: '',
        dropoff_marina_id: '',
        budget_min: '',
        budget_max: '',
        currency: 'AED',
        assigned_user_id: '',
        itinerary_notes: '',
        notes: '',
      }}
      action="/charter/enquiries"
      submitLabel="Create enquiry"
      cancelUrl="/charter/enquiries"
    />
  )
}
