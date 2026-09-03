import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function journeySections(props: { clients?: Option[] }): FormSection[] {
  return [
    {
      title: 'The journey',
      description: 'Usually opened automatically when a charter completes. This is for the ones that are not.',
      fields: [
        { name: 'client_id', label: 'Client', type: 'select', required: true, options: props.clients ?? [] },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'post_charter', label: 'Post-charter' },
            { value: 'post_sale', label: 'Post-sale' },
          ],
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['open', 'complete', 'lapsed'].map((value) => ({ value, label: value })),
        },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Open a journey"
      description="What happens after the money is settled."
      sections={journeySections(props)}
      initial={{ client_id: '', type: 'post_charter', status: 'open' }}
      action="/crm/journeys"
      submitLabel="Open journey"
      cancelUrl="/crm/journeys"
    />
  )
}
