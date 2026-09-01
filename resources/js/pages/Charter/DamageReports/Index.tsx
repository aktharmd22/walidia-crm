import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface DamageRow {
  id: number
  reference: string | null
  discovered_at: string | null
  description: string
  estimated_cost: string | null
  actual_cost: string | null
  deduct_from_deposit: boolean
  inspection_status: string
  status_tone: StatusTone
  is_closed: boolean
  booking?: { id: number; reference: string; url: string } | null
  yacht?: string | null
  url: string
}

export default function DamageReportsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<DamageRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<DamageRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Report',
      cell: ({ row }) => <IdentityCell name={row.original.reference ?? 'Damage'} subtitle={row.original.booking?.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'inspection_status',
      header: 'Inspection',
      cell: ({ row }) => (
        <StatusPill tone={row.original.status_tone}>{row.original.inspection_status.replace('_', ' ')}</StatusPill>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'estimated_cost',
      header: 'Estimate',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.estimated_cost ? <Money amount={row.original.estimated_cost} /> : <span className="text-ink-faint">—</span>,
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'discovered_at',
      header: 'Found',
      cell: ({ row }) => <DateText value={row.original.discovered_at} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex justify-end">
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/charter/damage-reports/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/charter/damage-reports/${row.original.id}`),
              },
            ]}
            trigger={
              <button
                type="button"
                onClick={(event) => event.stopPropagation()}
                className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
                aria-label="More actions"
              >
                <MoreHorizontal className="size-4" aria-hidden />
              </button>
            }
          />
        </span>
      ),
      meta: { priority: 1, align: 'end' },
    },
  ]

  return (
    <ResourceIndex<DamageRow>
      title={heading ?? 'Damage reports'}
      description="An open inspection holds the security deposit — which is exactly what it is for."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/damage-reports"
      createUrl="/charter/damage-reports/create"
      createLabel="Report damage"
      searchPlaceholder="Search reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'inspection_status',
          label: 'Inspection',
          options: [
            { value: '', label: 'Any' },
            { value: 'pending', label: 'Pending' },
            { value: 'in_progress', label: 'In progress' },
            { value: 'closed', label: 'Closed' },
          ],
        },
      ]}
      emptyTitle="No damage reported"
      emptyDescription="Damage found after a charter is recorded here, and holds the deposit until it is closed."
    />
  )
}
