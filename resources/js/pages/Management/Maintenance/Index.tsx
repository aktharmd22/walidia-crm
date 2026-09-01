import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface MaintenanceRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  management_agreement_id: number | null
  vendor: string | null
  vendor_id: number | null
  assigned_user_id: number | null
  category: string
  title: string
  description: string | null
  priority: string
  due_on: string | null
  started_at: string | null
  completed_at: string | null
  estimated_cost: string | null
  actual_cost: string | null
  currency: string
  owner_approval_required: boolean
  owner_approved_at: string | null
  blocks_charter: boolean
  is_overdue: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<MaintenanceRow>[] = [
  {
    id: 'title',
    header: 'Job',
    cell: ({ row }) => <IdentityCell name={row.original.title} subtitle={row.original.yacht} />,
  },
  {
    id: 'category',
    header: 'Category',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.category}</span>,
  },
  {
    id: 'due_on',
    header: 'Due',
    cell: ({ row }) => (
      <span className="flex items-center gap-2">
        <DateText value={row.original.due_on} />
        {row.original.is_overdue && <StatusPill tone="danger">Overdue</StatusPill>}
      </span>
    ),
  },
  {
    id: 'estimated_cost',
    header: 'Estimate',
    meta: { align: 'end' },
    cell: ({ row }) =>
      row.original.estimated_cost ? (
        <Money amount={row.original.estimated_cost} currency={row.original.currency} />
      ) : (
        <span className="text-ink-faint">—</span>
      ),
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status.replace(/_/g, ' ')}</StatusPill>,
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<MaintenanceRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Maintenance"
      description="Work on the managed fleet. A job that blocks charter says so."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/management/maintenance"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
