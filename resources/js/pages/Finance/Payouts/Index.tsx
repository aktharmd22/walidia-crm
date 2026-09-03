import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface PayoutRow {
  id: number
  reference: string | null
  transaction_id: number | null
  booking_id: number | null
  deal_id: number | null
  type: string
  payee_name: string
  payee_client_id: number | null
  payee_vendor_id: number | null
  amount: string
  currency: string
  amount_aed: string | null
  method: string
  bank_reference: string | null
  due_on: string | null
  approved_at: string | null
  paid_at: string | null
  is_paid: boolean
  is_overdue: boolean
  notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<PayoutRow>[] = [
  {
    id: 'payee_name',
    header: 'Payee',
    cell: ({ row }) => <IdentityCell name={row.original.payee_name} subtitle={row.original.reference} />,
  },
  {
    id: 'type',
    header: 'Type',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.type.replace(/_/g, ' ')}</span>,
  },
  {
    id: 'amount',
    header: 'Amount',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.amount} currency={row.original.currency} />,
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
  rows: Paginated<PayoutRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Payouts"
      description="Money leaving the company — sellers, co-brokers, referrers, vendors and crew."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/finance/payouts"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
