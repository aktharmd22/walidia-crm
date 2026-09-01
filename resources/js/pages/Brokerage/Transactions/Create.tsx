import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function transactionSections(props: { listings?: Option[]; clients?: Option[]; owners?: Option[] }): FormSection[] {
  return [
    {
      title: 'The sale',
      fields: [
        { name: 'listing_id', label: 'Listing', type: 'select', required: true, options: props.listings ?? [] },
        { name: 'buyer_client_id', label: 'Buyer', type: 'select', options: props.clients ?? [] },
        { name: 'seller_owner_id', label: 'Seller', type: 'select', options: props.owners ?? [] },
        { name: 'agreed_price', label: 'Agreed price', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['EUR', 'USD', 'AED', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'deposit_amount', label: 'Deposit', type: 'money' },
        { name: 'balance_amount', label: 'Balance', type: 'money' },
      ],
    },
    {
      title: 'Contract and closing',
      fields: [
        {
          name: 'contract_type',
          label: 'Contract',
          type: 'select',
          required: true,
          options: [
            { value: 'myba', label: 'MYBA' },
            { value: 'moa', label: 'Memorandum of Agreement' },
            { value: 'bespoke', label: 'Bespoke' },
          ],
        },
        { name: 'escrow_agent', label: 'Escrow agent' },
        { name: 'contract_signed_on', label: 'Contract signed', type: 'date' },
        { name: 'expected_closing_on', label: 'Expected closing', type: 'date' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'under_contract', 'funds_pending', 'transferring', 'completed', 'aborted'].map((value) => ({
            value,
            label: value.replace(/_/g, ' '),
          })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { listings?: Option[]; clients?: Option[]; owners?: Option[] }) {
  return (
    <ResourceForm
      title="Open a transaction"
      description="AML clearance and cleared funds are recorded on the transaction, and both gate the transfer."
      sections={transactionSections(props)}
      initial={{
        listing_id: '',
        buyer_client_id: '',
        seller_owner_id: '',
        agreed_price: '',
        currency: 'EUR',
        deposit_amount: '',
        balance_amount: '',
        contract_type: 'myba',
        escrow_agent: '',
        contract_signed_on: '',
        expected_closing_on: '',
        status: 'draft',
        notes: '',
      }}
      action="/brokerage/transactions"
      submitLabel="Open transaction"
      cancelUrl="/brokerage/transactions"
    />
  )
}
