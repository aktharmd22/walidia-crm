import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function offerSections(props: { clients?: Option[]; listings?: Option[] }): FormSection[] {
  return [
    {
      title: 'The offer',
      description: 'Drafted here; submitting it to the seller is a separate, guarded step.',
      fields: [
        { name: 'listing_id', label: 'Listing', type: 'select', required: true, options: props.listings ?? [] },
        { name: 'client_id', label: 'Buyer', type: 'select', required: true, options: props.clients ?? [] },
        { name: 'amount', label: 'Amount', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['EUR', 'USD', 'AED', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'deposit_amount', label: 'Deposit', type: 'money' },
        { name: 'valid_until', label: 'Valid until', type: 'date' },
        { name: 'subject_to_survey', label: 'Subject to survey', type: 'checkbox' },
        { name: 'subject_to_sea_trial', label: 'Subject to sea trial', type: 'checkbox' },
        {
          name: 'proof_of_funds_received',
          label: 'Proof of funds received',
          type: 'checkbox',
          wide: true,
          help: 'Where the mandate requires it, this is what unblocks submission.',
        },
        { name: 'conditions', label: 'Conditions', type: 'textarea', wide: true },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'submitted', 'countered', 'accepted', 'rejected', 'withdrawn', 'lapsed'].map((value) => ({
            value,
            label: value,
          })),
        },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Draft an offer"
      description="What the buyer is offering, and on what conditions."
      sections={offerSections(props)}
      initial={{
        listing_id: '',
        client_id: '',
        amount: '',
        currency: 'EUR',
        deposit_amount: '',
        valid_until: '',
        subject_to_survey: true,
        subject_to_sea_trial: true,
        proof_of_funds_received: false,
        conditions: '',
        status: 'draft',
      }}
      action="/brokerage/offers"
      submitLabel="Save offer"
      cancelUrl="/brokerage/offers"
    />
  )
}
