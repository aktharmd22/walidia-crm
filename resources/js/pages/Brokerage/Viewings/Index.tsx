import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface ViewingRow {
  id: number
  reference: string | null
  listing: string | null
  listing_id: number
  client: string | null
  client_id: number
  marina_id: number | null
  scheduled_at: string | null
  duration_minutes: number
  attendees: string | null
  feedback: string | null
  interest_level: number | null
  completed_at: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<ViewingRow>[] = [
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
    id: 'scheduled_at',
    header: 'When',
    cell: ({ row }) =>
      row.original.scheduled_at ? <DateText value={row.original.scheduled_at} withTime /> : <span className="text-ink-faint">Not scheduled</span>,
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
  rows: Paginated<ViewingRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Viewings"
      description="A buyer aboard someone else's yacht. Scheduling needs a signed NDA and a verified buyer."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/viewings"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
