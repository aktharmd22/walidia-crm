import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[]; owners?: Option[]; users?: Option[] }): FormSection[] {
  return [
    {
      title: 'The mandate',
      description: 'What we are allowed to sell, at what price, and until when.',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        { name: 'yacht_owner_id', label: 'Owner', type: 'select', options: props.owners ?? [] },
        {
          name: 'mandate_type',
          label: 'Mandate',
          type: 'select',
          required: true,
          options: [
            { value: 'central', label: 'Central agency' },
            { value: 'co_central', label: 'Co-central' },
            { value: 'open', label: 'Open listing' },
          ],
        },
        { name: 'asking_price', label: 'Asking price', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['EUR', 'USD', 'AED', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'reserve_price', label: 'Reserve', type: 'money', help: 'Visible only to those who can see amounts.' },
        { name: 'commission_rate', label: 'Commission %', type: 'number', required: true },
        { name: 'agreement_signed_on', label: 'Agreement signed', type: 'date' },
        { name: 'agreement_expires_on', label: 'Agreement expires', type: 'date', help: 'A lapsed mandate is a commission we cannot collect.' },
        { name: 'assigned_user_id', label: 'Broker', type: 'select', options: props.users ?? [] },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'active', 'under_offer', 'sold', 'withdrawn', 'expired'].map((value) => ({
            value,
            label: value.replace(/_/g, ' '),
          })),
        },
      ],
    },
    {
      title: 'What a buyer must do first',
      fields: [
        { name: 'requires_nda', label: 'Require a signed NDA before viewings', type: 'checkbox', wide: true },
        { name: 'requires_proof_of_funds', label: 'Require proof of funds before an offer', type: 'checkbox', wide: true },
        { name: 'is_published', label: 'Published to the website', type: 'checkbox', wide: true },
        { name: 'marketing_summary', label: 'Marketing summary', type: 'textarea', wide: true },
        { name: 'notes', label: 'Internal notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[]; owners?: Option[]; users?: Option[] }) {
  return (
    <ResourceForm
      title="Add a listing"
      description="Yachts we are mandated to sell, and how long that mandate has left to run."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        yacht_owner_id: '',
        mandate_type: 'central',
        asking_price: '',
        currency: 'EUR',
        reserve_price: '',
        commission_rate: 5,
        agreement_signed_on: '',
        agreement_expires_on: '',
        assigned_user_id: '',
        status: 'draft',
        requires_nda: true,
        requires_proof_of_funds: true,
        is_published: false,
        marketing_summary: '',
        notes: '',
      }}
      action="/brokerage/listings"
      submitLabel="Save listing"
      cancelUrl="/brokerage/listings"
    />
  )
}
