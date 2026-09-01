import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { IdentityCell, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface MarinaRow {
  id: number
  name: string
  country: string
  city: string | null
  timezone: string
  requires_manifest: boolean
  is_active: boolean
  status_tone: StatusTone
  berths_count?: number
  yachts_count?: number
  url: string
}

export default function MarinasIndex({
  rows,
  filters,
  can,
}: {
  rows: Paginated<MarinaRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  const columns: ColumnDef<MarinaRow, unknown>[] = [
    {
      id: 'name',
      header: 'Marina',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.name}
          subtitle={[row.original.city, row.original.country].filter(Boolean).join(', ')}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'timezone',
      header: 'Timezone',
      enableSorting: false,
      cell: ({ row }) => <span className="numeric">{row.original.timezone}</span>,
      meta: { priority: 2 },
    },
    {
      id: 'manifest',
      header: 'Manifest',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.requires_manifest ? (
          <StatusPill tone="warning">Required</StatusPill>
        ) : (
          <span className="text-ink-faint">Not required</span>
        ),
      meta: { priority: 3 },
    },
    {
      id: 'yachts_count',
      header: 'Yachts',
      enableSorting: false,
      cell: ({ row }) => <Num value={row.original.yachts_count ?? 0} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'is_active',
      header: 'Status',
      enableSorting: false,
      cell: ({ row }) => (
        <StatusPill tone={row.original.status_tone}>{row.original.is_active ? 'Active' : 'Inactive'}</StatusPill>
      ),
      meta: { priority: 1 },
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/fleet/marinas/${row.original.id}/edit`) },
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
    <ResourceIndex<MarinaRow>
      title="Marinas"
      description="Departure points, their timezones, and whether they require a guest manifest."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/fleet/marinas"
      createUrl="/fleet/marinas/create"
      createLabel="New marina"
      searchPlaceholder="Search marina or city…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      emptyTitle="No marinas yet"
      emptyDescription="Charter times are derived from the departure marina's timezone, so add the ones you sail from."
    />
  )
}
