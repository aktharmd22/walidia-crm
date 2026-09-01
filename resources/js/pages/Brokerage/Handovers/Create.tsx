import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { transactions?: Option[]; marinas?: Option[] }): FormSection[] {
  return [
    {
      title: 'The handover',
      description: 'Every item below has to be true before the handover can be closed.',
      fields: [
        { name: 'transaction_id', label: 'Transaction', type: 'select', required: true, options: props.transactions ?? [] },
        { name: 'marina_id', label: 'Marina', type: 'select', options: props.marinas ?? [] },
        { name: 'scheduled_at', label: 'Scheduled', type: 'datetime' },
        { name: 'keys_handed_over', label: 'Keys handed over', type: 'checkbox', wide: true },
        { name: 'documents_handed_over', label: 'Documents handed over', type: 'checkbox', wide: true },
        { name: 'inventory_signed', label: 'Inventory signed', type: 'checkbox', wide: true },
        { name: 'flag_registration_updated', label: 'Flag registration updated', type: 'checkbox', wide: true },
        { name: 'insurance_transferred', label: 'Insurance transferred', type: 'checkbox', wide: true },
        { name: 'outstanding_items', label: 'Outstanding', type: 'textarea', wide: true },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['pending', 'in_progress', 'completed'].map((value) => ({ value, label: value.replace(/_/g, ' ') })),
        },
      ],
    },
  ]
}

export default function Create(props: { transactions?: Option[]; marinas?: Option[] }) {
  return (
    <ResourceForm
      title="Schedule a handover"
      description="Keys, documents, inventory, flag, insurance. All five, or the sale is not finished."
      sections={sections(props)}
      initial={{
        transaction_id: '',
        marina_id: '',
        scheduled_at: '',
        keys_handed_over: false,
        documents_handed_over: false,
        inventory_signed: false,
        flag_registration_updated: false,
        insurance_transferred: false,
        outstanding_items: '',
        status: 'pending',
      }}
      action="/brokerage/handovers"
      submitLabel="Save handover"
      cancelUrl="/brokerage/handovers"
    />
  )
}
