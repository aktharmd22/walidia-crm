import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface AgreementRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  yacht_owner_id: number | null
  assigned_user_id: number | null
  scope: string
  fee_model: string
  monthly_fee?: string | null
  fee_percentage: string | null
  currency: string
  starts_on: string | null
  ends_on: string | null
  notice_days: number
  opex_budget_annual: string | null
  is_expiring: boolean
  notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<AgreementRow>[] = [
  {
    id: 'yacht',
    header: 'Yacht',
    cell: ({ row }) => <IdentityCell name={row.original.yacht ?? 'Yacht'} subtitle={row.original.reference} />,
  },
  {
    id: 'scope',
    header: 'Scope',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.scope.replace(/_/g, ' ')}</span>,
  },
  {
    id: 'monthly_fee',
    header: 'Fee',
    meta: { align: 'end' },
    cell: ({ row }) =>
      row.original.monthly_fee ? (
        <Money amount={row.original.monthly_fee} currency={row.original.currency} />
      ) : (
        <span className="text-ink-faint">Restricted</span>
      ),
  },
  {
    id: 'ends_on',
    header: 'Ends',
    cell: ({ row }) => (
      <span className="flex items-center gap-2">
        <DateText value={row.original.ends_on} />
        {row.original.is_expiring && <StatusPill tone="warning">Expiring</StatusPill>}
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
  rows: Paginated<AgreementRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Management agreements"
      description="What we run, for whom, on what fee — and when the mandate ends."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/management/agreements"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
