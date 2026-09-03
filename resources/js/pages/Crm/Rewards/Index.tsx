import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface RewardRow {
  id: number
  reference: string | null
  client: string | null
  client_id: number
  booking_id: number | null
  type: string
  value: string | null
  currency: string
  points: number | null
  code: string | null
  description: string | null
  valid_from: string | null
  expires_on: string | null
  redeemed_at: string | null
  is_redeemable: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<RewardRow>[] = [
  {
    id: 'client',
    header: 'Client',
    cell: ({ row }) => <IdentityCell name={row.original.client ?? '—'} subtitle={row.original.code} />,
  },
  {
    id: 'type',
    header: 'Type',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.type}</span>,
  },
  {
    id: 'value',
    header: 'Value',
    meta: { align: 'end' },
    cell: ({ row }) =>
      row.original.value ? (
        <Money amount={row.original.value} currency={row.original.currency} />
      ) : (
        <span className="numeric text-small text-ink-soft">{row.original.points ?? '—'}</span>
      ),
  },
  {
    id: 'expires_on',
    header: 'Expires',
    cell: ({ row }) => <DateText value={row.original.expires_on} />,
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
  rows: Paginated<RewardRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Loyalty and vouchers"
      description="Why a client comes back rather than shops around."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/crm/rewards"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
