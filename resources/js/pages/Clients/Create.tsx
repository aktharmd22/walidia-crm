import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function clientSections(
  companies: Option[],
  sources: Option[],
  users: Option[],
  canEditVip: boolean,
): FormSection[] {
  return [
    {
      title: 'Who they are',
      fields: [
        { name: 'salutation', label: 'Salutation', placeholder: 'H.E. · Mr · Ms · Capt' },
        { name: 'first_name', label: 'First name', required: true },
        { name: 'last_name', label: 'Last name' },
        { name: 'full_name_ar', label: 'Name in Arabic' },
        {
          name: 'client_type',
          label: 'Client type',
          type: 'multiselect',
          required: true,
          wide: true,
          help: 'A record can be several at once — a charter guest who later buys keeps one history.',
          options: [
            { value: 'charter_guest', label: 'Charter guest' },
            { value: 'buyer', label: 'Buyer' },
            { value: 'seller', label: 'Seller' },
            { value: 'owner', label: 'Owner' },
            { value: 'partner', label: 'Partner' },
          ],
        },
        { name: 'company_id', label: 'Company', type: 'select', options: companies },
        { name: 'position', label: 'Position' },
      ],
    },
    {
      title: 'How to reach them',
      fields: [
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'mobile', label: 'Mobile', type: 'tel', placeholder: '+971 50 123 4567' },
        { name: 'phone_alt', label: 'Alternative phone', type: 'tel' },
        {
          name: 'preferred_channel',
          label: 'Preferred channel',
          type: 'select',
          required: true,
          options: [
            { value: 'whatsapp', label: 'WhatsApp' },
            { value: 'email', label: 'Email' },
            { value: 'phone', label: 'Phone' },
            { value: 'agent', label: 'Through their agent' },
          ],
        },
        { name: 'nationality', label: 'Nationality' },
        { name: 'country', label: 'Country' },
        { name: 'city', label: 'City' },
        { name: 'emirate', label: 'Emirate' },
        { name: 'address_line1', label: 'Address', wide: true },
      ],
    },
    {
      title: 'Handling',
      fields: [
        {
          name: 'vip_level',
          label: 'VIP level',
          type: 'select',
          required: true,
          help: 'VIP and above restricts identity and dietary data to users with VIP access.',
          options: [
            { value: 'none', label: 'Standard' },
            { value: 'vip', label: 'VIP' },
            { value: 'uhnw', label: 'UHNW' },
            { value: 'protected', label: 'Protected' },
          ],
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: [
            { value: 'active', label: 'Active' },
            { value: 'pending_approval', label: 'Pending approval' },
            { value: 'dormant', label: 'Dormant' },
            { value: 'blacklisted', label: 'Blacklisted' },
          ],
        },
        { name: 'assigned_user_id', label: 'Owner', type: 'select', options: users },
        { name: 'source_id', label: 'Source', type: 'select', options: sources },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
    ...(canEditVip
      ? [
          {
            title: 'Identity and preferences',
            description: 'Encrypted at rest. Visible only to users with VIP access, and every read is logged.',
            fields: [
              { name: 'passport_number', label: 'Passport number' },
              { name: 'passport_expiry', label: 'Passport expiry', type: 'date' as const },
              { name: 'emirates_id', label: 'Emirates ID' },
              { name: 'date_of_birth', label: 'Date of birth', type: 'date' as const },
              { name: 'dietary_preferences', label: 'Dietary preferences', type: 'textarea' as const, wide: true },
              { name: 'allergies', label: 'Allergies', type: 'textarea' as const, wide: true, help: 'Read by the galley before every charter.' },
              { name: 'notes_vip', label: 'Confidential notes', type: 'textarea' as const, wide: true },
            ],
          },
        ]
      : []),
  ]
}

export default function ClientCreate({
  companies = [],
  sources = [],
  users = [],
  canEditVip = false,
}: {
  companies?: Option[]
  sources?: Option[]
  users?: Option[]
  canEditVip?: boolean
}) {
  return (
    <ResourceForm
      title="New client"
      description="One record per person. If they already exist, the duplicate check will say so before anything is saved."
      sections={clientSections(companies, sources, users, canEditVip)}
      initial={{
        salutation: '',
        first_name: '',
        last_name: '',
        full_name_ar: '',
        client_type: ['charter_guest'],
        company_id: '',
        position: '',
        email: '',
        mobile: '',
        phone_alt: '',
        preferred_channel: 'whatsapp',
        nationality: '',
        country: 'United Arab Emirates',
        city: '',
        emirate: '',
        address_line1: '',
        vip_level: 'none',
        status: 'active',
        assigned_user_id: '',
        source_id: '',
        notes: '',
        passport_number: '',
        passport_expiry: '',
        emirates_id: '',
        date_of_birth: '',
        dietary_preferences: '',
        allergies: '',
        notes_vip: '',
      }}
      action="/clients"
      submitLabel="Create client"
      cancelUrl="/clients"
    />
  )
}
