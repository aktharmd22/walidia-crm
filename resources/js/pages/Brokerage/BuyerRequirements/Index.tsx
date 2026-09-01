import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface BuyerRequirementRow {
  id: number
  reference: string | null
  client: string | null
  client_id: number
  budget_min: string | null
  budget_max: string | null
  currency: string
  loa_min: number | null
  loa_max: number | null
  year_from: number | null
  use_case: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<BuyerRequirementRow>[] = [
    {
      id: 'client',
      header: 'Buyer',
      cell: ({ row }) => <IdentityCell name={row.original.client ?? '—'} subtitle={row.original.reference} />,
    },
    {
      id: 'budget',
      header: 'Budget',
      meta: { align: 'end' },
      cell: ({ row }) =>
        row.original.budget_max ? (
          <Money amount={row.original.budget_max} currency={row.original.currency} />
        ) : (
          <span className="text-ink-faint">Open</span>
        ),
    },
    {
      id: 'loa',
      header: 'Length',
      cell: ({ row }) => (
        <span className="numeric text-small text-ink-soft">
          {row.original.loa_min ?? '—'}–{row.original.loa_max ?? '—'} m
        </span>
      ),
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
  rows: Paginated<BuyerRequirementRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Buyer requirements"
      description="The brief, written down once, so every broker matches against the same thing."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/buyer-requirements"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
