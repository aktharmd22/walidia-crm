import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { IdentityCell, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface YachtRow {
  id: number
  reference: string | null
  name: string
  builder: string | null
  model: string | null
  year_built: number | null
  loa_m: string | null
  capacity_cruising: number | null
  cabins: number | null
  roles: string[]
  status: string
  status_tone: StatusTone
  home_marina?: { id: number; name: string } | null
  charter_rates?: { full_day_rate: string | null; currency: string } | null
  asking_price?: string | null
  url: string
}

export default function FleetIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<YachtRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean; import?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<YachtRow, unknown>[] = [
    {
      id: 'name',
      header: 'Yacht',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.name}
          subtitle={[row.original.builder, row.original.model].filter(Boolean).join(' ') || row.original.reference}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'roles',
      header: 'Role',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex flex-wrap gap-1">
          {row.original.roles.length === 0 ? (
            <span className="text-ink-faint">—</span>
          ) : (
            row.original.roles.map((role) => (
              <span key={role} className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">
                {role}
              </span>
            ))
          )}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'loa_m',
      header: 'LOA',
      cell: ({ row }) => (row.original.loa_m ? <span className="numeric">{row.original.loa_m} m</span> : '—'),
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'capacity',
      header: 'Guests',
      enableSorting: false,
      cell: ({ row }) => <Num value={row.original.capacity_cruising ?? 0} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'year_built',
      header: 'Year',
      cell: ({ row }) => <Num value={row.original.year_built ?? 0} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'rate',
      header: 'Day rate',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.charter_rates?.full_day_rate ? (
          <Money amount={row.original.charter_rates.full_day_rate} currency={row.original.charter_rates.currency} />
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status.replace('_', ' ')}</StatusPill>,
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/fleet/yachts/${row.original.id}/edit`) },
              { key: 'availability', label: 'Availability', onSelect: () => router.visit('/fleet/availability') },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/fleet/yachts/${row.original.id}`),
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
    <ResourceIndex<YachtRow>
      title={heading ?? 'Fleet'}
      description="One record per hull. The same yacht can be chartered, listed for sale and managed at the same time."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/fleet/yachts"
      createUrl="/fleet/yachts/create"
      createLabel="Add yacht"
      searchPlaceholder="Search name, builder or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'role',
          label: 'Role',
          options: [
            { value: '', label: 'Any' },
            { value: 'charter', label: 'Charter fleet' },
            { value: 'sale', label: 'For sale' },
            { value: 'managed', label: 'Managed' },
          ],
        },
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'active', label: 'Active' },
            { value: 'maintenance', label: 'Maintenance' },
            { value: 'off_market', label: 'Off market' },
            { value: 'sold', label: 'Sold' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.name} subtitle={[row.builder, row.model].filter(Boolean).join(' ')} />
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.status.replace('_', ' ')}</StatusPill>
            {row.loa_m && <span className="numeric text-small text-ink-soft">{row.loa_m} m</span>}
          </div>
        </div>
      )}
      emptyTitle="No yachts in the registry"
      emptyDescription="Add a yacht once and flag what it is used for — charter, sale, management, or all three."
    />
  )
}
