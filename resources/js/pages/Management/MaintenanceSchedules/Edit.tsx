import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Management/MaintenanceSchedules/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[]; vendors?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        system: fv(record.system, 'engines'),
        title: fv(record.title),
        interval_days: fv(record.interval_days),
        interval_engine_hours: fv(record.interval_engine_hours),
        last_done_on: fv(record.last_done_on),
        next_due_on: fv(record.next_due_on),
        vendor_id: fv(record.vendor_id),
        budget_cost: fv(record.budget_cost),
        blocks_charter: fv(record.blocks_charter, false),
        is_active: fv(record.is_active, true),
        description: fv(record.description),
      }}
      action={`/management/maintenance-schedules/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/management/maintenance-schedules/${record.id}`}
    />
  )
}

export type { Option }
