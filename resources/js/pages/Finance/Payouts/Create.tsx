import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { clients?: Option[]; vendors?: Option[] }): FormSection[] {
  return [
    {
      title: 'Who is being paid',
      description: 'Approving a payout and paying it are two acts, deliberately by two people.',
      fields: [
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'seller', label: 'Seller' },
            { value: 'co_broker', label: 'Co-broker' },
            { value: 'referral', label: 'Referral' },
            { value: 'vendor', label: 'Vendor' },
            { value: 'crew', label: 'Crew' },
          ],
        },
        { name: 'payee_name', label: 'Payee', required: true, wide: true },
        { name: 'payee_client_id', label: 'Client record', type: 'select', options: props.clients ?? [] },
        { name: 'payee_vendor_id', label: 'Vendor record', type: 'select', options: props.vendors ?? [] },
        { name: 'amount', label: 'Amount', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        {
          name: 'method',
          label: 'Method',
          type: 'select',
          required: true,
          options: [
            { value: 'bank_transfer', label: 'Bank transfer' },
            { value: 'cheque', label: 'Cheque' },
            { value: 'cash', label: 'Cash' },
          ],
        },
        { name: 'due_on', label: 'Due', type: 'date' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['pending', 'approved', 'paid', 'cancelled'].map((value) => ({ value, label: value })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Raise a payout"
      description="Money leaving the company — sellers, co-brokers, referrers, vendors and crew."
      sections={sections(props)}
      initial={{
        type: 'seller',
        payee_name: '',
        payee_client_id: '',
        payee_vendor_id: '',
        amount: '',
        currency: 'AED',
        method: 'bank_transfer',
        due_on: '',
        status: 'pending',
        notes: '',
      }}
      action="/finance/payouts"
      submitLabel="Save"
      cancelUrl="/finance/payouts"
    />
  )
}
