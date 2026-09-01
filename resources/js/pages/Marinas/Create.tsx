import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

const timezones = [
  'Asia/Dubai',
  'Asia/Qatar',
  'Asia/Riyadh',
  'Asia/Muscat',
  'Indian/Mahe',
  'Indian/Maldives',
].map((zone) => ({ value: zone, label: zone }))

export function marinaSections(): FormSection[] {
  return [
    {
      title: 'The marina',
      fields: [
        { name: 'name', label: 'Name', required: true },
        { name: 'name_ar', label: 'Name in Arabic' },
        { name: 'country', label: 'Country', required: true },
        { name: 'emirate', label: 'Emirate / region' },
        { name: 'city', label: 'City' },
        {
          name: 'timezone',
          label: 'Timezone',
          type: 'select',
          required: true,
          options: timezones,
          help: 'Charter departure and return times are derived from this, not assumed.',
        },
        { name: 'latitude', label: 'Latitude', type: 'number' },
        { name: 'longitude', label: 'Longitude', type: 'number' },
      ],
    },
    {
      title: 'Contact and requirements',
      fields: [
        { name: 'contact_name', label: 'Contact name' },
        { name: 'contact_phone', label: 'Contact phone', type: 'tel' },
        { name: 'contact_email', label: 'Contact email', type: 'email' },
        { name: 'requires_manifest', label: 'Requires a guest manifest', type: 'checkbox' },
        {
          name: 'manifest_format',
          label: 'Manifest format',
          type: 'select',
          options: [
            { value: 'pdf', label: 'PDF' },
            { value: 'csv', label: 'CSV' },
            { value: 'xlsx', label: 'Excel' },
          ],
        },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function MarinaCreate() {
  return (
    <ResourceForm
      title="New marina"
      sections={marinaSections()}
      initial={{
        name: '',
        name_ar: '',
        country: 'United Arab Emirates',
        emirate: '',
        city: '',
        timezone: 'Asia/Dubai',
        latitude: '',
        longitude: '',
        contact_name: '',
        contact_phone: '',
        contact_email: '',
        requires_manifest: false,
        manifest_format: '',
        is_active: true,
        notes: '',
      }}
      action="/fleet/marinas"
      submitLabel="Create marina"
      cancelUrl="/fleet/marinas"
    />
  )
}
