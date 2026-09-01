import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function incidentSections(bookings: Option[], yachts: Option[]): FormSection[] {
  return [
    {
      title: 'What happened',
      description: 'Write it as it happened, at the time it happened. This record is what an insurer or the coastguard will read.',
      fields: [
        { name: 'booking_id', label: 'Charter', type: 'select', options: bookings },
        { name: 'yacht_id', label: 'Yacht', type: 'select', options: yachts },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'guest_injury', label: 'Guest injury' },
            { value: 'crew_injury', label: 'Crew injury' },
            { value: 'grounding', label: 'Grounding' },
            { value: 'collision', label: 'Collision' },
            { value: 'equipment_failure', label: 'Equipment failure' },
            { value: 'weather', label: 'Weather' },
            { value: 'guest_conduct', label: 'Guest conduct' },
            { value: 'other', label: 'Other' },
          ],
        },
        {
          name: 'severity',
          label: 'Severity',
          type: 'select',
          required: true,
          options: [
            { value: 'minor', label: 'Minor' },
            { value: 'moderate', label: 'Moderate' },
            { value: 'major', label: 'Major' },
            { value: 'critical', label: 'Critical' },
          ],
        },
        { name: 'occurred_at', label: 'Occurred at', type: 'datetime', required: true },
        { name: 'description', label: 'Description', type: 'textarea', required: true, wide: true },
        { name: 'immediate_action', label: 'Immediate action taken', type: 'textarea', wide: true },
      ],
    },
    {
      title: 'Who else knows',
      fields: [
        { name: 'injuries', label: 'Someone was injured', type: 'checkbox' },
        { name: 'authorities_notified', label: 'Authorities notified', type: 'checkbox' },
        { name: 'insurance_claim_ref', label: 'Insurance claim reference' },
      ],
    },
  ]
}

export default function IncidentCreate({ bookings = [], yachts = [] }: { bookings?: Option[]; yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Record an incident"
      description="Recorded now, closed later — with a written outcome, never a silent one."
      sections={incidentSections(bookings, yachts)}
      initial={{
        booking_id: '',
        yacht_id: '',
        type: 'other',
        severity: 'minor',
        occurred_at: new Date().toISOString().slice(0, 16),
        description: '',
        immediate_action: '',
        injuries: false,
        authorities_notified: false,
        insurance_claim_ref: '',
      }}
      action="/charter/incidents"
      submitLabel="Record incident"
      cancelUrl="/charter/incidents"
    />
  )
}
