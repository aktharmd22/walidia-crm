import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface HandoverRow {
  id: number
  reference: string | null
  transaction: string | null
  transaction_id: number
  marina_id: number | null
  scheduled_at: string | null
  completed_at: string | null
  keys_handed_over: boolean
  documents_handed_over: boolean
  inventory_signed: boolean
  flag_registration_updated: boolean
  insurance_transferred: boolean
  is_complete: boolean
  outstanding_items: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<HandoverRow>[] = [
    {
      id: 'reference',
      header: 'Handover',
      cell: ({ row }) => <IdentityCell name={row.original.transaction ?? 'Handover'} subtitle={row.original.reference} />,
    },
    {
      id: 'scheduled_at',
      header: 'Scheduled',
      cell: ({ row }) => <DateText value={row.original.scheduled_at} withTime />,
    },
    {
      id: 'progress',
      header: 'Items',
      cell: ({ row }) => {
        const done = [
          row.original.keys_handed_over,
          row.original.documents_handed_over,
          row.original.inventory_signed,
          row.original.flag_registration_updated,
          row.original.insurance_transferred,
        ].filter(Boolean).length
        return <span className="numeric text-small text-ink-soft">{done} / 5</span>
      },
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
  rows: Paginated<HandoverRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Handovers"
      description="Keys, documents, inventory, flag, insurance. All five, or the sale is not finished."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/handovers"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
