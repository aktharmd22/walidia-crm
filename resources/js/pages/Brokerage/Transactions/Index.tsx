import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface TransactionRow {
  id: number
  reference: string | null
  listing: string | null
  listing_id: number
  offer_id: number | null
  buyer: string | null
  buyer_client_id: number | null
  seller_owner_id: number | null
  agreed_price: string
  currency: string
  deposit_amount: string | null
  deposit_cleared_at: string | null
  balance_amount: string | null
  balance_cleared_at: string | null
  funds_cleared: boolean
  escrow_agent: string | null
  contract_type: string
  contract_signed_on: string | null
  expected_closing_on: string | null
  aml_cleared: boolean
  aml_cleared_at: string | null
  ownership_transferred_at: string | null
  is_transferred: boolean
  notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<TransactionRow>[] = [
  {
    id: 'reference',
    header: 'Transaction',
    cell: ({ row }) => <IdentityCell name={row.original.listing ?? 'Sale'} subtitle={row.original.reference} />,
  },
  {
    id: 'buyer',
    header: 'Buyer',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.buyer ?? '—'}</span>,
  },
  {
    id: 'agreed_price',
    header: 'Price',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.agreed_price} currency={row.original.currency} />,
  },
  {
    id: 'expected_closing_on',
    header: 'Closing',
    cell: ({ row }) => <DateText value={row.original.expected_closing_on} />,
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
  rows: Paginated<TransactionRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Transactions"
      description="Contract, escrow, AML, transfer. Ownership does not move until the money has cleared."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/transactions"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
