import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function damageSections(bookings: Option[], yachts: Option[]): FormSection[] {
  return [
    {
      title: 'The damage',
      description: 'Recording damage holds the security deposit until the inspection is closed. That is deliberate.',
      fields: [
        { name: 'booking_id', label: 'Charter', type: 'select', required: true, options: bookings },
        { name: 'yacht_id', label: 'Yacht', type: 'select', options: yachts },
        { name: 'discovered_at', label: 'Discovered at', type: 'datetime', required: true },
        { name: 'description', label: 'What is damaged', type: 'textarea', required: true, wide: true },
        { name: 'estimated_cost', label: 'Estimated cost', type: 'money' },
        {
          name: 'deduct_from_deposit',
          label: 'Intend to deduct from the security deposit',
          type: 'checkbox',
          wide: true,
          help: 'The final decision is made when the inspection is closed, not now.',
        },
      ],
    },
  ]
}

export default function DamageReportCreate({ bookings = [], yachts = [] }: { bookings?: Option[]; yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Record damage"
      description="Found at handover, photographed, and held against the deposit until resolved."
      sections={damageSections(bookings, yachts)}
      initial={{
        booking_id: '',
        yacht_id: '',
        discovered_at: new Date().toISOString().slice(0, 16),
        description: '',
        estimated_cost: '',
        deduct_from_deposit: false,
      }}
      action="/charter/damage-reports"
      submitLabel="Record damage"
      cancelUrl="/charter/damage-reports"
    />
  )
}
