import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface CertificateRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  type: string
  name: string
  number: string | null
  issued_by: string | null
  issued_on: string | null
  expires_on: string | null
  blocks_charter: boolean
  is_expired: boolean
  is_expiring: boolean
  notes: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<CertificateRow>[] = [
  {
    id: 'name',
    header: 'Certificate',
    cell: ({ row }) => <IdentityCell name={row.original.name} subtitle={row.original.yacht} />,
  },
  {
    id: 'number',
    header: 'Number',
    cell: ({ row }) => <span className="numeric text-small text-ink-soft">{row.original.number ?? '—'}</span>,
  },
  {
    id: 'expires_on',
    header: 'Expires',
    cell: ({ row }) => (
      <span className="flex items-center gap-2">
        <DateText value={row.original.expires_on} />
        {row.original.blocks_charter && row.original.is_expired && <StatusPill tone="danger">Blocks charter</StatusPill>}
      </span>
    ),
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => (
      <StatusPill tone={row.original.status_tone}>
        {row.original.is_expired ? 'Expired' : row.original.is_expiring ? 'Expiring' : 'Valid'}
      </StatusPill>
    ),
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<CertificateRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Certificates"
      description="The vessel's paperwork and the dates it dies. Dispatch reads this register."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/management/certificates"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
