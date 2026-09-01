import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { yachts?: Option[] }): FormSection[] {
  return [
    {
      title: 'The certificate',
      description: 'A certificate that blocks charter stops dispatch the moment it lapses.',
      fields: [
        { name: 'yacht_id', label: 'Yacht', type: 'select', required: true, options: props.yachts ?? [] },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'safety', label: 'Safety equipment' },
            { value: 'radio', label: 'Radio' },
            { value: 'load_line', label: 'Load line' },
            { value: 'registry', label: 'Registry' },
            { value: 'insurance', label: 'Insurance' },
            { value: 'mca', label: 'MCA compliance' },
            { value: 'flag', label: 'Flag' },
            { value: 'tonnage', label: 'Tonnage' },
          ],
        },
        { name: 'name', label: 'Name', required: true, wide: true },
        { name: 'number', label: 'Number' },
        { name: 'issued_by', label: 'Issued by' },
        { name: 'issued_on', label: 'Issued', type: 'date' },
        { name: 'expires_on', label: 'Expires', type: 'date' },
        { name: 'blocks_charter', label: 'A lapse blocks charter dispatch', type: 'checkbox', wide: true },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['valid', 'expiring', 'expired', 'renewing'].map((value) => ({ value, label: value })),
        },
        { name: 'notes', label: 'Notes', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Add a certificate"
      description="The vessel's paperwork and the dates it dies. Dispatch reads this register."
      sections={sections(props)}
      initial={{
        yacht_id: '',
        type: 'safety',
        name: '',
        number: '',
        issued_by: '',
        issued_on: '',
        expires_on: '',
        blocks_charter: true,
        status: 'valid',
        notes: '',
      }}
      action="/management/certificates"
      submitLabel="Save"
      cancelUrl="/management/certificates"
    />
  )
}
