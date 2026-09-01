import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { IdentityCell, Money, Percent } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated } from '@/types'

interface CostSheetRow {
  id: number
  reference: string
  status: string
  currency: string
  total_offer: string
  total_cost: string
  total_profit: string
  margin_pct: string
  effective_phase: string
  booking?: { id: number; reference: string; url: string } | null
  url: string
}

export default function CostSheetsIndex({
  rows,
  filters,
  can,
}: {
  rows: Paginated<CostSheetRow>
  filters: Record<string, unknown>
  can: { export?: boolean }
}) {
  const columns: ColumnDef<CostSheetRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Cost sheet',
      cell: ({ row }) => <IdentityCell name={row.original.reference} subtitle={row.original.booking?.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'phase',
      header: 'Phase',
      enableSorting: false,
      cell: ({ row }) => <StatusPill tone="info">{row.original.effective_phase}</StatusPill>,
      meta: { priority: 2 },
    },
    {
      id: 'total_offer',
      header: 'Offer',
      cell: ({ row }) => <Money amount={row.original.total_offer} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'total_cost',
      header: 'Cost',
      cell: ({ row }) => <Money amount={row.original.total_cost} currency={row.original.currency} />,
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'total_profit',
      header: 'Profit',
      cell: ({ row }) => <Money amount={row.original.total_profit} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'margin_pct',
      header: 'Margin',
      enableSorting: false,
      cell: ({ row }) => <Percent value={row.original.margin_pct} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
  ]

  return (
    <ResourceIndex<CostSheetRow>
      title="Cost sheets"
      description="Quote, invoice and actuals for every charter, in one artifact each."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/cost-sheets"
      searchPlaceholder="Search reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      emptyTitle="No cost sheets yet"
      emptyDescription="A cost sheet is created from a booking."
    />
  )
}
