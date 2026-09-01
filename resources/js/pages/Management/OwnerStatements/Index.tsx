import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface OwnerStatementRow {
  id: number
  reference: string | null
  management_agreement_id: number
  yacht: string | null
  yacht_id: number
  period_start: string | null
  period_end: string | null
  charter_revenue: string
  management_fee: string
  operating_costs: string
  maintenance_costs: string
  crew_costs: string
  net_to_owner: string
  currency: string
  issued_at: string | null
  approved_at: string | null
  paid_at: string | null
  notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<OwnerStatementRow>[] = [
  {
    id: 'yacht',
    header: 'Yacht',
    cell: ({ row }) => <IdentityCell name={row.original.yacht ?? 'Yacht'} subtitle={row.original.reference} />,
  },
  {
    id: 'period',
    header: 'Period',
    cell: ({ row }) => (
      <span className="flex items-center gap-1 text-small text-ink-soft">
        <DateText value={row.original.period_start} /> – <DateText value={row.original.period_end} />
      </span>
    ),
  },
  {
    id: 'charter_revenue',
    header: 'Revenue',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.charter_revenue} currency={row.original.currency} withCurrency={false} />,
  },
  {
    id: 'net_to_owner',
    header: 'Net to owner',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.net_to_owner} currency={row.original.currency} />,
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
  rows: Paginated<OwnerStatementRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Owner statements"
      description="What the owner earned this period, and what it cost. Issued deliberately, never by accident."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/management/owner-statements"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
