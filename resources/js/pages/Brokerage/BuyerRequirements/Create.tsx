import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { clients?: Option[] }): FormSection[] {
  return [
    {
      title: 'The brief',
      fields: [
        { name: 'client_id', label: 'Buyer', type: 'select', required: true, options: props.clients ?? [] },
        { name: 'budget_min', label: 'Budget from', type: 'money' },
        { name: 'budget_max', label: 'Budget to', type: 'money' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['EUR', 'USD', 'AED', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'loa_min', label: 'Length from (m)', type: 'number' },
        { name: 'loa_max', label: 'Length to (m)', type: 'number' },
        { name: 'year_from', label: 'Built from', type: 'number' },
        { name: 'use_case', label: 'Use', type: 'select', options: [
          { value: 'family', label: 'Family cruising' },
          { value: 'charter_income', label: 'Charter income' },
          { value: 'explorer', label: 'Explorer' },
          { value: 'entertaining', label: 'Entertaining' },
        ] },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['active', 'paused', 'fulfilled', 'lost'].map((value) => ({ value, label: value })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Add a buyer requirement"
      description="The brief, written down once, so every broker matches against the same thing."
      sections={sections(props)}
      initial={{
        client_id: '',
        budget_min: '',
        budget_max: '',
        currency: 'EUR',
        loa_min: '',
        loa_max: '',
        year_from: '',
        use_case: '',
        status: 'active',
        notes: '',
      }}
      action="/brokerage/buyer-requirements"
      submitLabel="Save requirement"
      cancelUrl="/brokerage/buyer-requirements"
    />
  )
}
