import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { agreements?: Option[]; yachts?: Option[] }): FormSection[] {
  return [
    {
      title: 'The period',
      fields: [
        { name: 'management_agreement_id', label: 'Agreement', type: 'select', required: true, options: props.agreements ?? [] },
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        { name: 'period_start', label: 'From', type: 'date', required: true },
        { name: 'period_end', label: 'To', type: 'date', required: true },
      ],
    },
    {
      title: 'The numbers',
      description: 'Net to the owner is derived: revenue less every cost they carry.',
      fields: [
        { name: 'charter_revenue', label: 'Charter revenue', type: 'money', required: true },
        { name: 'management_fee', label: 'Management fee', type: 'money', required: true },
        { name: 'operating_costs', label: 'Operating costs', type: 'money', required: true },
        { name: 'maintenance_costs', label: 'Maintenance', type: 'money', required: true },
        { name: 'crew_costs', label: 'Crew', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'issued', 'approved', 'paid'].map((value) => ({ value, label: value })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { agreements?: Option[]; yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Draft a statement"
      description="What the owner earned this period, and what it cost. Issued deliberately, never by accident."
      sections={sections(props)}
      initial={{
        management_agreement_id: '',
        yacht_id: '',
        period_start: '',
        period_end: '',
        charter_revenue: 0,
        management_fee: 0,
        operating_costs: 0,
        maintenance_costs: 0,
        crew_costs: 0,
        currency: 'AED',
        status: 'draft',
        notes: '',
      }}
      action="/management/owner-statements"
      submitLabel="Save"
      cancelUrl="/management/owner-statements"
    />
  )
}
