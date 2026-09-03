import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface JourneyRow {
  id: number
  client: string | null
  client_id: number
  booking_id: number | null
  transaction_id: number | null
  type: string
  thank_you_sent_at: string | null
  feedback_requested_at: string | null
  review_requested_at: string | null
  survey_sent_at: string | null
  satisfaction_score: number | null
  survey_response: string | null
  complaint_raised: boolean
  complaint_detail: string | null
  complaint_resolved_at: string | null
  complaint_resolution: string | null
  has_open_complaint: boolean
  follow_ups_sent: Record<string, string> | null
  upsell_interests: string[] | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<JourneyRow>[] = [
  {
    id: 'client',
    header: 'Client',
    cell: ({ row }) => <IdentityCell name={row.original.client ?? '—'} subtitle={row.original.type.replace(/_/g, ' ')} />,
  },
  {
    id: 'satisfaction',
    header: 'Satisfaction',
    cell: ({ row }) =>
      row.original.satisfaction_score ? (
        <span className="numeric text-body text-ink">{row.original.satisfaction_score} / 5</span>
      ) : (
        <span className="text-ink-faint">Not asked</span>
      ),
  },
  {
    id: 'progress',
    header: 'Sent',
    cell: ({ row }) => {
      const done = [
        row.original.thank_you_sent_at,
        row.original.feedback_requested_at,
        row.original.review_requested_at,
      ].filter(Boolean).length
      return <span className="numeric text-small text-ink-soft">{done} / 3</span>
    },
  },
  {
    id: 'complaint',
    header: 'Complaint',
    cell: ({ row }) =>
      row.original.has_open_complaint ? (
        <StatusPill tone="danger">Open</StatusPill>
      ) : row.original.complaint_raised ? (
        <StatusPill tone="success">Resolved</StatusPill>
      ) : (
        <span className="text-ink-faint">None</span>
      ),
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
  },
]

/**
 * What happens after the money is settled — the part most systems leave to a
 * spreadsheet, and the part where repeat business is won or lost.
 */
export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<JourneyRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Client journeys"
      description="Thank you, feedback, review, complaints and the follow-ups that bring a client back."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/crm/journeys"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      filterFields={[
        {
          key: 'type',
          label: 'Type',
          options: [
            { value: '', label: 'Any' },
            { value: 'post_charter', label: 'Post-charter' },
            { value: 'post_sale', label: 'Post-sale' },
          ],
        },
        {
          key: 'complaint_raised',
          label: 'Complaint',
          options: [
            { value: '', label: 'Any' },
            { value: '1', label: 'Raised' },
            { value: '0', label: 'None' },
          ],
        },
      ]}
      emptyTitle="No journeys yet"
      emptyDescription="A journey opens automatically when a charter completes."
    />
  )
}
