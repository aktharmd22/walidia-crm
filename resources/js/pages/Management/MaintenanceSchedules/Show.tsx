import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { MaintenanceScheduleRow } from '@/pages/Management/MaintenanceSchedules/Index'

export default function Show({
  record,
  can,
}: {
  record: MaintenanceScheduleRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.title}
      subtitle={record.yacht}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/management/maintenance-schedules/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/management/maintenance-schedules/${record.id}` : undefined}
      backUrl="/management/maintenance-schedules"
      facts={[
        { label: 'System', value: record.system.replace(/_/g, ' ') },
        { label: 'Every', value: record.interval_days ? `${record.interval_days} days` : '—' },
        { label: 'Engine hours', value: record.interval_engine_hours ? `${record.interval_engine_hours} h` : '—' },
        { label: 'Last done', value: <DateText value={record.last_done_on} /> },
        { label: 'Next due', value: <DateText value={record.next_due_on} /> },
        { label: 'Budget', value: record.budget_cost ? <Money amount={record.budget_cost} /> : '—' },
      ]}
    />
  )
}
