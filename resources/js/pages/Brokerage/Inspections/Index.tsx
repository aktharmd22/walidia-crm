import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface InspectionRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  listing_id: number | null
  handover_id: number | null
  type: string
  inspected_at: string | null
  hull_condition: number | null
  engine_condition: number | null
  interior_condition: number | null
  systems_condition: number | null
  findings: string | null
  recommended_works: string | null
  estimated_works_cost: string | null
  outcome: string | null
  is_clear: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<InspectionRow>[] = [
  {
    id: 'yacht',
    header: 'Yacht',
    cell: ({ row }) => <IdentityCell name={row.original.yacht ?? 'Yacht'} subtitle={row.original.reference} />,
  },
  {
    id: 'type',
    header: 'Type',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.type.replace(/_/g, ' ')}</span>,
  },
  {
    id: 'inspected_at',
    header: 'Inspected',
    cell: ({ row }) => <DateText value={row.original.inspected_at} />,
  },
  {
    id: 'works',
    header: 'Works',
    meta: { align: 'end' },
    cell: ({ row }) =>
      row.original.estimated_works_cost ? (
        <Money amount={row.original.estimated_works_cost} />
      ) : (
        <span className="text-ink-faint">—</span>
      ),
  },
  {
    id: 'outcome',
    header: 'Outcome',
    cell: ({ row }) =>
      row.original.outcome ? (
        <StatusPill tone={row.original.status_tone}>{row.original.outcome}</StatusPill>
      ) : (
        <span className="text-ink-faint">Pending</span>
      ),
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<InspectionRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Inspections"
      description="A yacht looked over before it is listed, or before it is delivered."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/inspections"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
