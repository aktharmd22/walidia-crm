import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function viewingSections(props: { clients?: Option[]; listings?: Option[]; marinas?: Option[] }): FormSection[] {
  return [
    {
      title: 'The viewing',
      description: 'Request it now; scheduling is a separate, guarded step on the viewing itself.',
      fields: [
        { name: 'listing_id', label: 'Listing', type: 'select', required: true, options: props.listings ?? [] },
        { name: 'client_id', label: 'Buyer', type: 'select', required: true, options: props.clients ?? [] },
        { name: 'marina_id', label: 'Where', type: 'select', options: props.marinas ?? [] },
        { name: 'scheduled_at', label: 'Proposed time', type: 'datetime' },
        { name: 'duration_minutes', label: 'Duration (minutes)', type: 'number' },
        { name: 'attendees', label: 'Attending', help: 'Who is coming aboard, by name.' },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          required: true,
          options: ['requested', 'scheduled', 'completed', 'cancelled', 'no_show'].map((value) => ({
            value,
            label: value.replace(/_/g, ' '),
          })),
        },
      ],
    },
  ]
}

export default function Create(props: { clients?: Option[]; listings?: Option[]; marinas?: Option[] }) {
  return (
    <ResourceForm
      title="Request a viewing"
      description="The buyer's NDA and ID are checked when the viewing is scheduled, not when it is requested."
      sections={viewingSections(props)}
      initial={{
        listing_id: '',
        client_id: '',
        marina_id: '',
        scheduled_at: '',
        duration_minutes: 90,
        attendees: '',
        status: 'requested',
      }}
      action="/brokerage/viewings"
      submitLabel="Request viewing"
      cancelUrl="/brokerage/viewings"
    />
  )
}
