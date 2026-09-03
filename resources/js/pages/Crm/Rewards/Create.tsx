import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { clients?: Option[] }): FormSection[] {
  return [
    {
      title: 'The reward',
      description: 'The code is generated on issue — a voucher nobody can quote is a voucher nobody redeems.',
      fields: [
        { name: 'client_id', label: 'Client', type: 'select', required: true, options: props.clients ?? [] },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'voucher', label: 'Gift voucher' },
            { value: 'points', label: 'Loyalty points' },
            { value: 'upgrade', label: 'Upgrade' },
            { value: 'membership', label: 'Membership' },
          ],
        },
        { name: 'value', label: 'Value', type: 'money' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP'].map((code) => ({ value: code, label: code })),
        },
        { name: 'points', label: 'Points', type: 'number' },
        { name: 'valid_from', label: 'Valid from', type: 'date' },
        { name: 'expires_on', label: 'Expires', type: 'date' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['issued', 'redeemed', 'expired', 'cancelled'].map((value) => ({ value, label: value })),
        },
        { name: 'description', label: 'What it is for', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Issue a reward"
      description="Why a client comes back rather than shops around."
      sections={sections(props)}
      initial={{
        client_id: '',
        type: 'voucher',
        value: '',
        currency: 'AED',
        points: '',
        valid_from: new Date().toISOString().slice(0, 10),
        expires_on: '',
        status: 'issued',
        description: '',
      }}
      action="/crm/rewards"
      submitLabel="Save"
      cancelUrl="/crm/rewards"
    />
  )
}
