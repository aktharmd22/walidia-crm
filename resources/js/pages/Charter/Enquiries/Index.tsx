import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Compass, MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface EnquiryRow {
  id: number
  reference: string
  experience_type: string | null
  requested_date: string | null
  guest_count: number
  budget_max: string | null
  currency: string
  status: string
  status_tone: StatusTone
  client?: { id: number; name: string; kyc_status: string } | null
  marina?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  url: string
}

export default function EnquiriesIndex({
  rows,
  filters,
  can,
}: {
  rows: Paginated<EnquiryRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  const columns: ColumnDef<EnquiryRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Enquiry',
      cell: ({ row }) => (
        <IdentityCell name={row.original.client?.name ?? row.original.reference} subtitle={row.original.reference} />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'experience_type',
      header: 'Experience',
      enableSorting: false,
      cell: ({ row }) => (row.original.experience_type ?? '—').replace(/_/g, ' '),
      meta: { priority: 2 },
    },
    {
      id: 'requested_date',
      header: 'Requested',
      cell: ({ row }) => <DateText value={row.original.requested_date} />,
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
      id: 'budget',
      header: 'Budget',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.budget_max ? (
          <Money amount={row.original.budget_max} currency={row.original.currency} compact />
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          <button
            type="button"
            onClick={(event) => {
              event.stopPropagation()
              router.visit(`/charter/enquiries/${row.original.id}/matching`)
            }}
            className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
            aria-label="Find yachts"
          >
            <Compass className="size-4" aria-hidden />
          </button>
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'match', label: 'Find yachts', onSelect: () => router.visit(`/charter/enquiries/${row.original.id}/matching`) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/charter/enquiries/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/charter/enquiries/${row.original.id}`),
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
    <ResourceIndex<EnquiryRow>
      title="Charter enquiries"
      description="What clients have asked for, and where each request has got to."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/enquiries"
      createUrl="/charter/enquiries/create"
      createLabel="New enquiry"
      searchPlaceholder="Search reference, client or experience…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'new', label: 'New' },
            { value: 'matching', label: 'Matching' },
            { value: 'proposed', label: 'Proposed' },
            { value: 'won', label: 'Won' },
            { value: 'lost', label: 'Lost' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.client?.name ?? row.reference} subtitle={row.reference} />
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.status}</StatusPill>
            <DateText value={row.requested_date} className="text-small text-ink-soft" />
          </div>
        </div>
      )}
      emptyTitle="No enquiries yet"
      emptyDescription="Convert a lead, or log an enquiry directly from a call."
    />
  )
}
