import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function depositSections(bookings: Option[]): FormSection[] {
  return [
    {
      title: 'The hold',
      description: 'A card pre-authorisation and a cash deposit are the same promise to the client — both are released the same way.',
      fields: [
        { name: 'booking_id', label: 'Charter', type: 'select', required: true, options: bookings },
        { name: 'amount', label: 'Amount', type: 'money', required: true },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'OMR'].map((code) => ({ value: code, label: code })),
        },
        {
          name: 'method',
          label: 'Method',
          type: 'select',
          required: true,
          options: [
            { value: 'card_hold', label: 'Card pre-authorisation' },
            { value: 'cash', label: 'Cash' },
            { value: 'transfer', label: 'Bank transfer' },
          ],
        },
      ],
    },
  ]
}

export default function SecurityDepositCreate({ bookings = [] }: { bookings?: Option[] }) {
  return (
    <ResourceForm
      title="Record a security deposit"
      description="Recorded as held. Releasing it is blocked while any damage inspection on the charter is open."
      sections={depositSections(bookings)}
      initial={{ booking_id: '', amount: '', currency: 'AED', method: 'card_hold' }}
      action="/finance/security-deposits"
      submitLabel="Record deposit"
      cancelUrl="/finance/security-deposits"
    />
  )
}
