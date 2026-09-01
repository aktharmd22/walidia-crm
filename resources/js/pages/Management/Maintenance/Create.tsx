import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[]; vendors?: Option[] }): FormSection[] {
  return [
    {
      title: 'The work',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        { name: 'title', label: 'What needs doing', required: true, wide: true },
        {
          name: 'category',
          label: 'Category',
          type: 'select',
          required: true,
          options: ['routine', 'repair', 'refit', 'warranty', 'survey'].map((value) => ({ value, label: value })),
        },
        {
          name: 'priority',
          label: 'Priority',
          type: 'select',
          required: true,
          options: ['low', 'normal', 'high', 'critical'].map((value) => ({ value, label: value })),
        },
        { name: 'vendor_id', label: 'Vendor', type: 'select', options: props.vendors ?? [] },
        { name: 'due_on', label: 'Due', type: 'date' },
        { name: 'estimated_cost', label: 'Estimate', type: 'money' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'owner_approval_required', label: "Needs the owner's approval", type: 'checkbox', wide: true },
        { name: 'blocks_charter', label: 'Blocks charter while open', type: 'checkbox', wide: true },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['open', 'scheduled', 'in_progress', 'done', 'cancelled'].map((value) => ({
            value,
            label: value.replace(/_/g, ' '),
          })),
        },
        { name: 'description', label: 'Detail', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Raise a job"
      description="Work on the managed fleet. A job that blocks charter says so."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        title: '',
        category: 'routine',
        priority: 'normal',
        vendor_id: '',
        due_on: '',
        estimated_cost: '',
        currency: 'AED',
        owner_approval_required: false,
        blocks_charter: false,
        status: 'open',
        description: '',
      }}
      action="/management/maintenance"
      submitLabel="Save"
      cancelUrl="/management/maintenance"
    />
  )
}
