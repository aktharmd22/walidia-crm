import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections } from '@/pages/Automation/MessageTemplates/Create'

export default function Edit({
  record,
}: { record: Record<string, unknown> & { id: number } }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections()}
      initial={{
        key: fv(record.key),
        name: fv(record.name),
        channel: fv(record.channel, 'email'),
        category: fv(record.category, 'client'),
        subject_en: fv(record.subject_en),
        body_en: fv(record.body_en),
        subject_ar: fv(record.subject_ar),
        body_ar: fv(record.body_ar),
        is_active: fv(record.is_active, true),
      }}
      action={`/engine/message-templates/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/engine/message-templates/${record.id}`}
    />
  )
}
