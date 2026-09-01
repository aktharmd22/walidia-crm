import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[] }): FormSection[] {
  return [
    {
      title: 'The mandate',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        {
          name: 'scope',
          label: 'Scope',
          type: 'select',
          required: true,
          options: [
            { value: 'full', label: 'Full management' },
            { value: 'technical', label: 'Technical only' },
            { value: 'crew_only', label: 'Crew only' },
            { value: 'charter_only', label: 'Charter only' },
          ],
        },
        {
          name: 'fee_model',
          label: 'Fee model',
          type: 'select',
          required: true,
          options: [
            { value: 'fixed', label: 'Fixed monthly' },
            { value: 'percentage', label: 'Percentage of revenue' },
            { value: 'hybrid', label: 'Hybrid' },
          ],
        },
        { name: 'monthly_fee', label: 'Monthly fee', type: 'money' },
        { name: 'fee_percentage', label: 'Fee %', type: 'number' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'starts_on', label: 'Starts', type: 'date', required: true },
        { name: 'ends_on', label: 'Ends', type: 'date' },
        { name: 'notice_days', label: 'Notice period (days)', type: 'number' },
        { name: 'opex_budget_annual', label: 'Annual opex budget', type: 'money' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'active', 'expiring', 'ended'].map((value) => ({ value, label: value })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Add a management agreement"
      description="What we run, for whom, on what fee — and when the mandate ends."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        scope: 'full',
        fee_model: 'fixed',
        monthly_fee: '',
        fee_percentage: '',
        currency: 'AED',
        starts_on: '',
        ends_on: '',
        notice_days: 90,
        opex_budget_annual: '',
        status: 'active',
        notes: '',
      }}
      action="/management/agreements"
      submitLabel="Save"
      cancelUrl="/management/agreements"
    />
  )
}
