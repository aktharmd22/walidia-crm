import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function vendorSections(categories: Option[]): FormSection[] {
  return [
    {
      title: 'The company',
      description: 'A vendor can be recorded now and approved later — but only an approved vendor can take a purchase order.',
      fields: [
        { name: 'legal_name', label: 'Legal name', required: true, wide: true },
        { name: 'trade_name', label: 'Trading as' },
        { name: 'vendor_category_id', label: 'Category', type: 'select', options: categories },
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
      ],
    },
    {
      title: 'Licence and tax',
      description: 'A vendor whose trade licence has lapsed is a compliance problem on a client invoice.',
      fields: [
        { name: 'trn', label: 'TRN', help: '15 digits, as issued by the FTA.' },
        { name: 'trade_licence_no', label: 'Trade licence number' },
        { name: 'licence_expiry', label: 'Licence expires', type: 'date' },
        { name: 'payment_terms_days', label: 'Payment terms (days)', type: 'number' },
      ],
    },
    {
      title: 'Who to call',
      fields: [
        { name: 'contact_name', label: 'Contact name' },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'mobile', label: 'Mobile', type: 'tel' },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function VendorCreate({ categories = [] }: { categories?: Option[] }) {
  return (
    <ResourceForm
      title="Add a vendor"
      description="Caterers, florists, jet-ski hire, transfers — everyone who supplies a charter."
      sections={vendorSections(categories)}
      initial={{
        legal_name: '',
        trade_name: '',
        vendor_category_id: '',
        status: 'active',
        trn: '',
        trade_licence_no: '',
        licence_expiry: '',
        payment_terms_days: 30,
        contact_name: '',
        email: '',
        mobile: '',
        notes: '',
      }}
      action="/vendors"
      submitLabel="Add vendor"
      cancelUrl="/vendors"
    />
  )
}
