import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface CommunicationRow {
  id: number
  reference: string | null
  client: string | null
  client_id: number | null
  channel: string
  direction: string
  to_address: string | null
  subject: string | null
  body: string | null
  sent_at: string | null
  delivered_at: string | null
  read_at: string | null
  failure_reason: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<CommunicationRow>[] = [
  {
    id: 'client',
    header: 'To',
    cell: ({ row }) => <IdentityCell name={row.original.client ?? row.original.to_address ?? '—'} subtitle={row.original.subject} />,
  },
  {
    id: 'channel',
    header: 'Channel',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.channel}</span>,
  },
  {
    id: 'sent_at',
    header: 'Sent',
    cell: ({ row }) => <DateText value={row.original.sent_at} withTime />,
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
  },
]

/**
 * The ledger of everything the company has said to a client.
 *
 * Read-only, deliberately: "did you tell me?" is a question this business gets
 * asked, and the answer is worth nothing if someone can edit it afterwards.
 */
export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<CommunicationRow>
  filters: Record<string, unknown>
  can: { export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Communications"
      description="Everything the company has sent, and what became of it. Written by the system, never edited."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/engine/communications"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      filterFields={[
        {
          key: 'channel',
          label: 'Channel',
          options: [
            { value: '', label: 'Any' },
            { value: 'email', label: 'Email' },
            { value: 'whatsapp', label: 'WhatsApp' },
            { value: 'sms', label: 'SMS' },
          ],
        },
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'sent', label: 'Sent' },
            { value: 'delivered', label: 'Delivered' },
            { value: 'read', label: 'Read' },
            { value: 'failed', label: 'Failed' },
          ],
        },
      ]}
      emptyTitle="Nothing sent yet"
      emptyDescription="Messages appear here the moment an automation rule fires."
    />
  )
}
