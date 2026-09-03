import { DetailShell } from '@/components/crud/DetailShell'
import type { MessageTemplateRow } from '@/pages/Automation/MessageTemplates/Index'

export default function Show({
  record,
  can,
}: {
  record: MessageTemplateRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.name}
      subtitle={record.key}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/engine/message-templates/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/engine/message-templates/${record.id}` : undefined}
      backUrl="/engine/message-templates"
      facts={[
        { label: 'Channel', value: record.channel },
        { label: 'Audience', value: record.category },
        { label: 'Subject', value: record.subject_en ?? '—' },
        { label: 'Arabic', value: record.body_ar ? 'Written' : 'Not written' },
      ]}
    />
  )
}
