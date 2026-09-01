import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface NdaRow {
  id: number
  reference: string | null
  listing: string | null
  listing_id: number | null
  client: string | null
  client_id: number
  scope: string
  signed_at: string | null
  expires_on: string | null
  is_signed: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<NdaRow>[] = [
    {
      id: 'client',
      header: 'Buyer',
      cell: ({ row }) => <IdentityCell name={row.original.client ?? '—'} subtitle={row.original.reference} />,
    },
    {
      id: 'listing',
      header: 'Listing',
      cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.listing ?? 'Whole fleet'}</span>,
    },
    {
      id: 'signed_at',
      header: 'Signed',
      cell: ({ row }) => <DateText value={row.original.signed_at} />,
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
  rows: Paginated<NdaRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="NDAs"
      description="Signed before anything is shown. Every viewing reads this record first."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/ndas"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
