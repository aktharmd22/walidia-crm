import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal, ShieldCheck } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface BookingRow {
  id: number
  reference: string
  status: string
  status_label: string
  status_tone: StatusTone
  starts_local: string
  starts_at: string
  guest_count: number
  is_released: boolean
  currency: string
  value?: string | null
  client?: { id: number; name: string } | null
  yacht?: { id: number; name: string } | null
  marina?: { id: number; name: string } | null
  url: string
}

export default function BookingsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<BookingRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<BookingRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Charter',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.yacht?.name ?? row.original.reference}
          subtitle={row.original.client?.name ?? row.original.reference}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <StatusPill tone={row.original.status_tone}>{row.original.status_label}</StatusPill>
          {row.original.is_released && (
            <span title="Operational Release granted">
              <ShieldCheck className="size-4 text-success" aria-hidden />
            </span>
          )}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'starts_at',
      header: 'Departs',
      cell: ({ row }) => <span className="numeric">{row.original.starts_local}</span>,
      meta: { priority: 1, align: 'end' },
    },
    {
      id: 'guests',
      header: 'Guests',
      enableSorting: false,
      cell: ({ row }) => <Num value={row.original.guest_count} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'value',
      header: 'Value',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.value ? (
          <Money amount={row.original.value} currency={row.original.currency} />
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 2, align: 'end', numeric: true },
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/charter/bookings/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/charter/bookings/${row.original.id}`),
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
    <ResourceIndex<BookingRow>
      title={heading ?? 'Bookings'}
      description="Confirmed and pending charters. The shield marks the ones Finance has released for operations."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/bookings"
      searchPlaceholder="Search reference, client or yacht…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'pending_contract', label: 'Pending contract' },
            { value: 'deposit_pending', label: 'Deposit pending' },
            { value: 'confirmed', label: 'Confirmed' },
            { value: 'in_progress', label: 'In progress' },
            { value: 'completed', label: 'Completed' },
            { value: 'cancelled', label: 'Cancelled' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.yacht?.name ?? row.reference} subtitle={row.client?.name} />
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.status_label}</StatusPill>
            <DateText value={row.starts_at} className="text-small text-ink-soft" />
          </div>
        </div>
      )}
      emptyTitle="No bookings yet"
      emptyDescription="A booking opens when a client accepts a proposal."
    />
  )
}
