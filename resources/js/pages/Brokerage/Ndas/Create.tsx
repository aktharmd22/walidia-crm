import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { clients?: Option[]; listings?: Option[] }): FormSection[] {
  return [
    {
      title: 'The agreement',
      description: 'Until this is signed, no viewing on this listing can be scheduled.',
      fields: [
        { name: 'client_id', label: 'Buyer', type: 'select', required: true, options: props.clients ?? [] },
        { name: 'listing_id', label: 'Listing', type: 'select', options: props.listings ?? [], help: 'Leave empty for a fleet-wide NDA.' },
        {
          name: 'scope',
          label: 'Scope',
          type: 'select',
          required: true,
          options: [
            { value: 'listing', label: 'This listing' },
            { value: 'fleet', label: 'Whole fleet' },
          ],
        },
        { name: 'sent_at', label: 'Sent', type: 'datetime' },
        { name: 'expires_on', label: 'Expires', type: 'date' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['draft', 'sent', 'signed', 'expired'].map((value) => ({ value, label: value })),
        },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Raise an NDA"
      description="Signed before anything is shown. Every viewing reads this record first."
      sections={sections(props)}
      initial={{ client_id: '', listing_id: '', scope: 'listing', sent_at: '', expires_on: '', status: 'draft' }}
      action="/brokerage/ndas"
      submitLabel="Save NDA"
      cancelUrl="/brokerage/ndas"
    />
  )
}
