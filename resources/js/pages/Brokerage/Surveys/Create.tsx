import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { listings?: Option[] }): FormSection[] {
  return [
    {
      title: 'The survey',
      fields: [
        { name: 'listing_id', label: 'Listing', type: 'select', options: props.listings ?? [] },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'condition', label: 'Condition survey' },
            { value: 'sea_trial', label: 'Sea trial' },
            { value: 'valuation', label: 'Valuation' },
          ],
        },
        { name: 'surveyor_name', label: 'Surveyor' },
        { name: 'surveyor_company', label: 'Company' },
        { name: 'scheduled_at', label: 'Scheduled', type: 'datetime' },
        { name: 'cost', label: 'Cost', type: 'money' },
        {
          name: 'paid_by',
          label: 'Paid by',
          type: 'select',
          required: true,
          options: [
            { value: 'buyer', label: 'Buyer' },
            { value: 'seller', label: 'Seller' },
            { value: 'shared', label: 'Shared' },
          ],
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['scheduled', 'in_progress', 'completed', 'cancelled'].map((value) => ({ value, label: value.replace(/_/g, ' ') })),
        },
      ],
    },
  ]
}

export default function Create(props: { listings?: Option[] }) {
  return (
    <ResourceForm
      title="Schedule a survey"
      description="The findings that reopen a price — recorded where both sides can see them."
      sections={sections(props)}
      initial={{
        listing_id: '',
        type: 'condition',
        surveyor_name: '',
        surveyor_company: '',
        scheduled_at: '',
        cost: '',
        paid_by: 'buyer',
        status: 'scheduled',
      }}
      action="/brokerage/surveys"
      submitLabel="Save survey"
      cancelUrl="/brokerage/surveys"
    />
  )
}
