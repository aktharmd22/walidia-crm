import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(props: { templates?: Option[] }): FormSection[] {
  return [
    {
      title: 'When it fires',
      description: 'An event happens, or the clock reaches a point relative to a date on the record.',
      fields: [
        { name: 'key', label: 'Key', required: true },
        { name: 'name', label: 'Name', required: true },
        {
          name: 'business_line',
          label: 'Line',
          type: 'select',
          required: true,
          options: ['charter', 'brokerage', 'management'].map((value) => ({ value, label: value })),
        },
        {
          name: 'trigger_type',
          label: 'Trigger',
          type: 'select',
          required: true,
          options: [
            { value: 'event', label: 'When something happens' },
            { value: 'schedule', label: 'Relative to a date' },
          ],
        },
        {
          name: 'trigger_event',
          label: 'Event',
          type: 'select',
          options: [
            { value: 'lead.created', label: 'Lead created' },
            { value: 'booking.confirmed', label: 'Booking confirmed' },
            { value: 'crew.dispatched', label: 'Crew dispatched' },
            { value: 'charter.completed', label: 'Charter completed' },
            { value: 'deposit.released', label: 'Deposit released' },
          ],
        },
        {
          name: 'anchor_field',
          label: 'Relative to',
          type: 'select',
          options: [
            { value: 'starts_at', label: 'Charter start' },
            { value: 'ends_at', label: 'Charter end' },
            { value: 'due_at', label: 'Payment due date' },
            { value: 'agreement_expires_on', label: 'Listing agreement expiry' },
            { value: 'ownership_transferred_at', label: 'Ownership transfer' },
          ],
        },
        {
          name: 'offset_hours',
          label: 'Offset (hours)',
          type: 'number',
          required: true,
          help: 'Negative is before. −24 is the day before; 168 is a week after.',
        },
      ],
    },
    {
      title: 'What it does',
      fields: [
        {
          name: 'action',
          label: 'Action',
          type: 'select',
          required: true,
          options: [
            { value: 'send_message', label: 'Send a message' },
            { value: 'create_task', label: 'Create a task' },
            { value: 'notify_role', label: 'Notify a role' },
            { value: 'update_field', label: 'Update a field' },
          ],
        },
        { name: 'message_template_id', label: 'Template', type: 'select', options: props.templates ?? [] },
        {
          name: 'audience',
          label: 'Audience',
          type: 'select',
          required: true,
          options: ['client', 'owner', 'crew', 'vendor', 'role'].map((value) => ({ value, label: value })),
        },
        { name: 'is_active', label: 'Active', type: 'checkbox', wide: true },
        { name: 'description', label: 'What this is for', type: 'textarea', wide: true },
      ],
    },
  ]
}

export default function Create(props: { templates?: Option[] }) {
  return (
    <ResourceForm
      title="Add a rule"
      description="When the system speaks. Rules are data — moving a reminder is an edit, not a deployment."
      sections={sections(props)}
      initial={{
        key: '',
        name: '',
        business_line: 'charter',
        trigger_type: 'event',
        trigger_event: '',
        anchor_field: '',
        offset_hours: 0,
        action: 'send_message',
        message_template_id: '',
        audience: 'client',
        is_active: true,
        description: '',
      }}
      action="/engine/workflows"
      submitLabel="Save"
      cancelUrl="/engine/workflows"
    />
  )
}
