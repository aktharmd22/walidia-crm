import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function companySections(users: Option[], types: Option[]): FormSection[] {
  return [
    {
      title: 'The company',
      fields: [
        { name: 'legal_name', label: 'Legal name', required: true },
        { name: 'trade_name', label: 'Trading name' },
        { name: 'type', label: 'Type', type: 'select', required: true, options: types },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: [
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
            { value: 'blacklisted', label: 'Blacklisted' },
          ],
        },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'phone', label: 'Phone', type: 'tel' },
        { name: 'website', label: 'Website', placeholder: 'https://' },
        { name: 'assigned_user_id', label: 'Relationship owner', type: 'select', options: users },
      ],
    },
    {
      title: 'Trade and tax',
      description: 'The TRN appears on every tax invoice issued to this company.',
      fields: [
        { name: 'trn', label: 'TRN' },
        { name: 'trade_licence_no', label: 'Trade licence number' },
        { name: 'licence_expiry', label: 'Licence expiry', type: 'date' },
        { name: 'billing_email', label: 'Billing email', type: 'email' },
        { name: 'payment_terms_days', label: 'Payment terms (days)', type: 'number' },
        { name: 'commission_rate_default', label: 'Default commission %', type: 'number' },
      ],
    },
    {
      title: 'Address',
      fields: [
        { name: 'address_line1', label: 'Address', wide: true },
        { name: 'city', label: 'City' },
        { name: 'emirate', label: 'Emirate' },
        { name: 'country', label: 'Country' },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function CompanyCreate({ users = [], types = [] }: { users?: Option[]; types?: Option[] }) {
  return (
    <ResourceForm
      title="New company"
      sections={companySections(users, types)}
      initial={{
        legal_name: '',
        trade_name: '',
        type: 'corporate',
        status: 'active',
        email: '',
        phone: '',
        website: '',
        assigned_user_id: '',
        trn: '',
        trade_licence_no: '',
        licence_expiry: '',
        billing_email: '',
        payment_terms_days: 0,
        commission_rate_default: '',
        address_line1: '',
        city: '',
        emirate: '',
        country: 'United Arab Emirates',
        notes: '',
      }}
      action="/companies"
      submitLabel="Create company"
      cancelUrl="/companies"
    />
  )
}
