import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { IdentityCell, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface CrewRow {
  id: number
  reference: string | null
  full_name: string
  role: string
  employment_type: string
  nationality: string | null
  mobile: string | null
  day_rate?: string | null
  currency: string
  status: string
  status_tone: StatusTone
  has_expired_documents: boolean
  expiring_soon: number
  url: string
}

export default function CrewIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<CrewRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<CrewRow, unknown>[] = [
    {
      id: 'full_name',
      header: 'Crew',
      cell: ({ row }) => <IdentityCell name={row.original.full_name} subtitle={row.original.role} />,
      meta: { priority: 1 },
    },
    {
      id: 'documents',
      header: 'Documents',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.has_expired_documents ? (
          <StatusPill tone="danger">Expired</StatusPill>
        ) : row.original.expiring_soon > 0 ? (
          <StatusPill tone="warning">
            <Num value={row.original.expiring_soon} /> expiring
          </StatusPill>
        ) : (
          <StatusPill tone="success">Valid</StatusPill>
        ),
      meta: { priority: 1 },
    },
    {
      id: 'employment_type',
      header: 'Type',
      enableSorting: false,
      cell: ({ row }) => row.original.employment_type,
      meta: { priority: 3 },
    },
    {
      id: 'day_rate',
      header: 'Day rate',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.day_rate ? (
          <Money amount={row.original.day_rate} currency={row.original.currency} />
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status.replace('_', ' ')}</StatusPill>,
      meta: { priority: 2 },
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/crew/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/crew/${row.original.id}`),
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
    <ResourceIndex<CrewRow>
      title={heading ?? 'Crew'}
      description="Captains, engineers and interior crew — and whose paperwork is about to lapse."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/crew"
      createUrl="/crew/create"
      createLabel="Add crew"
      searchPlaceholder="Search name, role or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'role',
          label: 'Role',
          options: [
            { value: '', label: 'Any' },
            { value: 'captain', label: 'Captain' },
            { value: 'engineer', label: 'Engineer' },
            { value: 'deckhand', label: 'Deckhand' },
            { value: 'steward', label: 'Steward' },
            { value: 'chef', label: 'Chef' },
          ],
        },
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'active', label: 'Active' },
            { value: 'on_leave', label: 'On leave' },
            { value: 'inactive', label: 'Inactive' },
          ],
        },
      ]}
      emptyTitle="No crew yet"
      emptyDescription="Add the people who actually run the charters, with their documents and expiry dates."
    />
  )
}
