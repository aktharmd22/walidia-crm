import { DetailShell } from '@/components/crud/DetailShell'
import type { WorkflowRow } from '@/pages/Automation/Workflows/Index'

export default function Show({
  record,
  can,
}: {
  record: WorkflowRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.name}
      subtitle={record.key}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/engine/workflows/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/engine/workflows/${record.id}` : undefined}
      backUrl="/engine/workflows"
      facts={[
        { label: 'Line', value: record.business_line },
        { label: 'Trigger', value: record.trigger_type },
        { label: 'Event', value: record.trigger_event ?? '—' },
        { label: 'Relative to', value: record.anchor_field ?? '—' },
        { label: 'Offset', value: <span className="numeric">{record.offset_hours} h</span> },
        { label: 'Action', value: record.action.replace(/_/g, ' ') },
        { label: 'Template', value: record.template ?? '—' },
        { label: 'Audience', value: record.audience },
      ]}
    />
  )
}
