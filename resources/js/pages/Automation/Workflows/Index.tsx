import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface WorkflowRow {
  id: number
  key: string
  name: string
  description: string | null
  business_line: string
  trigger_type: string
  trigger_event: string | null
  anchor_field: string | null
  offset_hours: number
  action: string
  audience: string
  message_template_id: number | null
  template: string | null
  conditions: unknown[] | null
  is_active: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<WorkflowRow>[] = [
  {
    id: 'name',
    header: 'Rule',
    cell: ({ row }) => <IdentityCell name={row.original.name} subtitle={row.original.key} />,
  },
  {
    id: 'trigger',
    header: 'Fires',
    cell: ({ row }) => (
      <span className="text-small text-ink-soft">
        {row.original.trigger_type === 'event'
          ? row.original.trigger_event
          : `${row.original.offset_hours > 0 ? '+' : ''}${row.original.offset_hours}h from ${row.original.anchor_field}`}
      </span>
    ),
  },
  {
    id: 'action',
    header: 'Does',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.action.replace(/_/g, ' ')}</span>,
  },
  {
    id: 'audience',
    header: 'To',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.audience}</span>,
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<WorkflowRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Automation rules"
      description="When the system speaks. Rules are data — moving a reminder is an edit, not a deployment."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/engine/workflows"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
