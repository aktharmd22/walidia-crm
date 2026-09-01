import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface ListingRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  mandate_type: string
  asking_price: string
  currency: string
  commission_rate: string
  agreement_expires_on: string | null
  agreement_active: boolean
  agreement_expiring: boolean
  is_published: boolean
  status: string
  status_tone: StatusTone
  assignee: string | null
  url: string
}

const columns: ColumnDef<ListingRow>[] = [
    {
      id: 'reference',
      header: 'Listing',
      cell: ({ row }) => <IdentityCell name={row.original.yacht ?? 'Yacht'} subtitle={row.original.reference} />,
    },
    {
      id: 'mandate_type',
      header: 'Mandate',
      cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.mandate_type.replace(/_/g, ' ')}</span>,
    },
    {
      id: 'asking_price',
      header: 'Asking',
      meta: { align: 'end' },
      cell: ({ row }) => <Money amount={row.original.asking_price} currency={row.original.currency} />,
    },
    {
      id: 'agreement_expires_on',
      header: 'Mandate ends',
      cell: ({ row }) =>
        row.original.agreement_expires_on ? (
          <span className="flex items-center gap-2">
            <DateText value={row.original.agreement_expires_on} />
            {!row.original.agreement_active && <StatusPill tone="danger">Expired</StatusPill>}
            {row.original.agreement_active && row.original.agreement_expiring && <StatusPill tone="warning">Expiring</StatusPill>}
          </span>
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
  rows: Paginated<ListingRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Listings"
      description="Yachts we are mandated to sell, and how long that mandate has left to run."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/listings"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
