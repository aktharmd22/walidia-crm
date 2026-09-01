import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { IdentityCell, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface VendorRow {
  id: number
  reference: string | null
  legal_name: string
  trade_name: string | null
  display_name: string
  category?: string | null
  contact_name: string | null
  email: string | null
  mobile: string | null
  payment_terms_days: number
  rating_avg: string | null
  is_approved: boolean
  status: string
  status_tone: StatusTone
  url: string
}

export default function VendorsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<VendorRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<VendorRow, unknown>[] = [
    {
      id: 'legal_name',
      header: 'Vendor',
      cell: ({ row }) => <IdentityCell name={row.original.display_name} subtitle={row.original.category ?? row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'is_approved',
      header: 'Approval',
      enableSorting: false,
      cell: ({ row }) => (
        <StatusPill tone={row.original.is_approved ? 'success' : 'warning'}>
          {row.original.is_approved ? 'Approved' : 'Not approved'}
        </StatusPill>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'rating_avg',
      header: 'Rating',
      cell: ({ row }) =>
        row.original.rating_avg ? <span className="numeric">{row.original.rating_avg}</span> : <span className="text-ink-faint">—</span>,
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'payment_terms_days',
      header: 'Terms',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="numeric">
          <Num value={row.original.payment_terms_days} /> d
        </span>
      ),
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'contact',
      header: 'Contact',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex flex-col">
          <span className="text-body text-ink">{row.original.contact_name ?? '—'}</span>
          <span className="text-small text-ink-faint">{row.original.mobile ?? row.original.email ?? ''}</span>
        </span>
      ),
      meta: { priority: 3 },
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/vendors/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/vendors/${row.original.id}`),
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
    <ResourceIndex<VendorRow>
      title={heading ?? 'Vendors'}
      description="Caterers, watersports, transfers and technical suppliers. Only approved vendors take purchase orders."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/vendors"
      createUrl="/vendors/create"
      createLabel="Add vendor"
      searchPlaceholder="Search vendor name or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
            { value: 'blacklisted', label: 'Blacklisted' },
          ],
        },
      ]}
      emptyTitle="No vendors yet"
      emptyDescription="Add the suppliers a charter depends on, with their licence expiry and payment terms."
    />
  )
}
