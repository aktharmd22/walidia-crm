import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface ValuationRow {
  id: number
  reference: string | null
  yacht: string | null
  yacht_id: number
  listing_id: number | null
  valued_on: string | null
  market_low: string | null
  market_high: string | null
  market_spread: number | null
  broker_valuation: string
  recommended_asking: string | null
  agreed_asking: string | null
  currency: string
  rationale: string | null
  pricing_decision: string
  adjustment_reason: string | null
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<ValuationRow>[] = [
  {
    id: 'yacht',
    header: 'Yacht',
    cell: ({ row }) => <IdentityCell name={row.original.yacht ?? 'Yacht'} subtitle={row.original.reference} />,
  },
  {
    id: 'broker_valuation',
    header: 'Broker valuation',
    meta: { align: 'end' },
    cell: ({ row }) => <Money amount={row.original.broker_valuation} currency={row.original.currency} />,
  },
  {
    id: 'market',
    header: 'Market range',
    meta: { align: 'end' },
    cell: ({ row }) =>
      row.original.market_low && row.original.market_high ? (
        <span className="numeric text-small text-ink-soft">
          {row.original.market_low} – {row.original.market_high}
        </span>
      ) : (
        <span className="text-ink-faint">—</span>
      ),
  },
  {
    id: 'valued_on',
    header: 'Valued',
    cell: ({ row }) => <DateText value={row.original.valued_on} />,
  },
  {
    id: 'pricing_decision',
    header: 'Decision',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.pricing_decision}</StatusPill>,
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<ValuationRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Valuations"
      description="What a yacht is worth and the working behind it — an asking price a broker can defend."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/brokerage/valuations"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
