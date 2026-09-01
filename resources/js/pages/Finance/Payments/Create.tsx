import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function paymentSections(clients: Option[]): FormSection[] {
  return [
    {
      title: 'The payment',
      description: 'Recording a payment does not clear it — Finance confirms that separately, and only then does it count.',
      fields: [
        { name: 'client_id', label: 'Client', type: 'select', options: clients },
        {
          name: 'method',
          label: 'Method',
          type: 'select',
          required: true,
          options: [
            { value: 'bank_transfer', label: 'Bank transfer' },
            { value: 'card', label: 'Card' },
            { value: 'cash', label: 'Cash' },
            { value: 'cheque', label: 'Cheque' },
            { value: 'link', label: 'Payment link' },
          ],
        },
        { name: 'amount', label: 'Amount', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'OMR'].map((code) => ({ value: code, label: code })),
        },
        {
          name: 'exchange_rate',
          label: 'Exchange rate to AED',
          type: 'number',
          help: 'Captured at the transaction date, not looked up later.',
        },
        { name: 'received_at', label: 'Received', type: 'datetime', required: true },
        { name: 'gateway_reference', label: 'Bank or gateway reference' },
        { name: 'bank_charge_amount', label: 'Bank charge', type: 'money' },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function PaymentCreate({ clients = [] }: { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Record a payment"
      description="What arrived, from whom, and when."
      sections={paymentSections(clients)}
      initial={{
        client_id: '',
        method: 'bank_transfer',
        amount: '',
        currency: 'AED',
        exchange_rate: 1,
        received_at: new Date().toISOString().slice(0, 16),
        gateway_reference: '',
        bank_charge_amount: '',
        notes: '',
      }}
      action="/finance/payments"
      submitLabel="Record payment"
      cancelUrl="/finance/payments"
    />
  )
}
