import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface SurveyRow {
  id: number
  reference: string | null
  listing: string | null
  listing_id: number | null
  offer_id: number | null
  type: string
  surveyor_name: string | null
  surveyor_company: string | null
  scheduled_at: string | null
  completed_at: string | null
  cost: string | null
  paid_by: string
  outcome: string | null
  findings: string | null
  remediation_estimate: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<SurveyRow>[] = [
    {
      id: 'reference',
      header: 'Survey',
      cell: ({ row }) => <IdentityCell name={row.original.type.replace(/_/g, ' ')} subtitle={row.original.reference} />,
    },
    {
      id: 'listing',
      header: 'Listing',
      cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.listing ?? '—'}</span>,
    },
    {
      id: 'scheduled_at',
      header: 'Scheduled',
      cell: ({ row }) => <DateText value={row.original.scheduled_at} withTime />,
    },
    {
      id: 'outcome',
      header: 'Outcome',
      cell: ({ row }) =>
        row.original.outcome ? (
          <StatusPill tone={row.original.status_tone}>{row.original.outcome}</StatusPill>
        ) : (
          <span className="text-ink-faint">Pending</span>
        ),
    },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<SurveyRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Surveys and sea trials"
      description="The findings that reopen a price — recorded where both sides can see them."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/surveys"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
