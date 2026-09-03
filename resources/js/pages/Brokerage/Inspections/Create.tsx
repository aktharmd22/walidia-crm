import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[]; listings?: Option[] }): FormSection[] {
  const condition = [5, 4, 3, 2, 1].map((score) => ({ value: score, label: `${score} — ${['unusable', 'poor', 'fair', 'good', 'excellent'][score - 1]}` }))

  return [
    {
      title: 'The inspection',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        { name: 'listing_id', label: 'Listing', type: 'select', options: props.listings ?? [] },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'listing', label: 'Before listing' },
            { value: 'pre_delivery', label: 'Pre-delivery' },
          ],
        },
        { name: 'inspected_at', label: 'Inspected at', type: 'datetime' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['scheduled', 'in_progress', 'completed', 'cancelled'].map((value) => ({ value, label: value.replace(/_/g, ' ') })),
        },
      ],
    },
    {
      title: 'Condition',
      description: 'Five is showroom, one is unusable. The scores are what a later buyer argues with.',
      fields: [
        { name: 'hull_condition', label: 'Hull', type: 'select', options: condition },
        { name: 'engine_condition', label: 'Engines', type: 'select', options: condition },
        { name: 'interior_condition', label: 'Interior', type: 'select', options: condition },
        { name: 'systems_condition', label: 'Systems', type: 'select', options: condition },
        {
          name: 'outcome',
          label: 'Outcome',
          type: 'select',
          options: [
            { value: 'clear', label: 'Clear' },
            { value: 'defects', label: 'Defects found' },
            { value: 'failed', label: 'Failed' },
          ],
        },
        { name: 'estimated_works_cost', label: 'Estimated works', type: 'money' },
        { name: 'findings', label: 'Findings', type: 'textarea', wide: true },
        { name: 'recommended_works', label: 'Recommended works', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Record an inspection"
      description="A yacht looked over before it is listed, or before it is delivered."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        listing_id: '',
        type: 'listing',
        inspected_at: new Date().toISOString().slice(0, 16),
        status: 'scheduled',
        hull_condition: '',
        engine_condition: '',
        interior_condition: '',
        systems_condition: '',
        outcome: '',
        estimated_works_cost: '',
        findings: '',
        recommended_works: '',
      }}
      action="/brokerage/inspections"
      submitLabel="Save"
      cancelUrl="/brokerage/inspections"
    />
  )
}
