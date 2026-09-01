import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface OfferRow {
  id: number
  reference: string | null
  listing: string | null
  listing_id: number
  client: string | null
  client_id: number
  amount: string
  currency: string
  deposit_amount: string | null
  subject_to_survey: boolean
  subject_to_sea_trial: boolean
  proof_of_funds_received: boolean
  valid_until: string | null
  conditions: string | null
  submitted_at: string | null
  responded_at: string | null
  response_notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<OfferRow>[] = [
  {
    id: 'client',
    header: 'Buyer',
    cell: ({ row }) => <IdentityCell name={row.original.client ?? '—'} subtitle={row.original.reference} />,
  },
  {
    id: 'listing',
    header: 'Listing',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.listing ?? '—'}</span>,
  },
  {
    id: 'amount',
    header: 'Offer',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.amount} currency={row.original.currency} />,
  },
  {
    id: 'valid_until',
    header: 'Valid to',
    cell: ({ row }) => <DateText value={row.original.valid_until} />,
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
  rows: Paginated<OfferRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Offers"
      description="Offers on listings. Submitting one needs proof of funds where the mandate demands it."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/offers"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
