import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Automation/Workflows/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { templates?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { templates?: Option[] })}
      initial={{
        key: fv(record.key),
        name: fv(record.name),
        business_line: fv(record.business_line, 'charter'),
        trigger_type: fv(record.trigger_type, 'event'),
        trigger_event: fv(record.trigger_event),
        anchor_field: fv(record.anchor_field),
        offset_hours: fv(record.offset_hours, 0),
        action: fv(record.action, 'send_message'),
        message_template_id: fv(record.message_template_id),
        audience: fv(record.audience, 'client'),
        is_active: fv(record.is_active, true),
        description: fv(record.description),
      }}
      action={`/engine/workflows/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/engine/workflows/${record.id}`}
    />
  )
}

export type { Option }
