import { ResourceForm, fv, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

const sections = (marinas: Option[]): FormSection[] => [
  {
    title: 'The charter',
    fields: [
      { name: 'starts_at', label: 'Departs', type: 'datetime', required: true },
      { name: 'ends_at', label: 'Returns', type: 'datetime', required: true },
      { name: 'departure_marina_id', label: 'Departure marina', type: 'select', options: marinas },
      { name: 'return_marina_id', label: 'Return marina', type: 'select', options: marinas },
      { name: 'guests_adults', label: 'Adults', type: 'number', required: true },
      { name: 'guests_children', label: 'Children', type: 'number', required: true },
      { name: 'apa_amount', label: 'APA', type: 'money' },
      { name: 'itinerary', label: 'Itinerary', type: 'textarea', wide: true },
      { name: 'special_requests', label: 'Special requests', type: 'textarea', wide: true },
    ],
  },
]

export default function BookingEdit({
  record,
  marinas = [],
}: {
  record: Record<string, unknown> & { id: number; reference: string }
  marinas?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.reference}`}
      description="Times are stored UTC and shown in the departure marina's timezone."
      sections={sections(marinas)}
      initial={{
        starts_at: String(record.starts_at ?? '').slice(0, 16),
        ends_at: String(record.ends_at ?? '').slice(0, 16),
        departure_marina_id: (record.marina as { id: number } | null)?.id ?? '',
        return_marina_id: '',
        guests_adults: fv(record.guests_adults, 0),
        guests_children: fv(record.guests_children, 0),
        apa_amount: fv(record.apa_amount),
        itinerary: fv(record.itinerary),
        special_requests: fv(record.special_requests),
      }}
      action={`/charter/bookings/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/charter/bookings/${record.id}`}
    />
  )
}
