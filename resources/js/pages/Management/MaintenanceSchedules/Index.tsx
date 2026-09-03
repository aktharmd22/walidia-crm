import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface MaintenanceScheduleRow {
  id: number
  yacht: string | null
  yacht_id: number
  vendor_id: number | null
  system: string
  title: string
  description: string | null
  interval_days: number | null
  interval_engine_hours: number | null
  last_done_on: string | null
  next_due_on: string | null
  is_due: boolean
  budget_cost: string | null
  blocks_charter: boolean
  is_active: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<MaintenanceScheduleRow>[] = [
  {
    id: 'title',
    header: 'Work',
    cell: ({ row }) => <IdentityCell name={row.original.title} subtitle={row.original.yacht} />,
  },
  {
    id: 'system',
    header: 'System',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.system.replace(/_/g, ' ')}</span>,
  },
  {
    id: 'interval',
    header: 'Every',
    cell: ({ row }) => (
      <span className="numeric text-small text-ink-soft">
        {row.original.interval_days ? `${row.original.interval_days} d` : '—'}
        {row.original.interval_engine_hours ? ` / ${row.original.interval_engine_hours} h` : ''}
      </span>
    ),
  },
  {
    id: 'next_due_on',
    header: 'Next due',
    cell: ({ row }) => (
      <span className="flex items-center gap-2">
        <DateText value={row.original.next_due_on} />
        {row.original.is_due && <StatusPill tone="danger">Due</StatusPill>}
      </span>
    ),
  },
  {
    id: 'blocks_charter',
    header: 'Blocks charter',
    cell: ({ row }) =>
      row.original.blocks_charter ? <StatusPill tone="warning">Yes</StatusPill> : <span className="text-ink-faint">No</span>,
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<MaintenanceScheduleRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Maintenance schedules"
      description="Preventive work that recurs — whichever comes first, the calendar or the engine hours."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/management/maintenance-schedules"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
