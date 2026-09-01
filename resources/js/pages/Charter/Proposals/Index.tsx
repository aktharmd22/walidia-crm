import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal, Send } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface ProposalRow {
  id: number
  reference: string
  version: number
  status: string
  status_tone: StatusTone
  valid_until: string | null
  has_expired: boolean
  total: string
  currency: string
  client?: { id: number; name: string } | null
  enquiry?: { id: number; reference: string; url: string } | null
  url: string
}

export default function ProposalsIndex({
  rows,
  filters,
  can,
}: {
  rows: Paginated<ProposalRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  const columns: ColumnDef<ProposalRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Proposal',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.client?.name ?? row.original.reference}
          subtitle={`${row.original.reference} · v${row.original.version}`}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>
          {row.original.has_expired && <StatusPill tone="neutral">Expired</StatusPill>}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'total',
      header: 'Total',
      cell: ({ row }) => <Money amount={row.original.total} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'valid_until',
      header: 'Valid to',
      enableSorting: false,
      cell: ({ row }) => <DateText value={row.original.valid_until} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          {row.original.status === 'draft' && (
            <button
              type="button"
              onClick={(event) => {
                event.stopPropagation()
                router.post(`/charter/proposals/${row.original.id}/send`, {}, { preserveScroll: true })
              }}
              className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
              aria-label="Send proposal"
            >
              <Send className="size-4" aria-hidden />
            </button>
          )}
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'version', label: 'New version', onSelect: () => router.post(`/charter/proposals/${row.original.id}/version`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/charter/proposals/${row.original.id}`),
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
    <ResourceIndex<ProposalRow>
      title="Proposals"
      description="Priced offers. A sent proposal is versioned rather than edited, so what the client saw is always on file."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/proposals"
      searchPlaceholder="Search reference or client…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'draft', label: 'Draft' },
            { value: 'sent', label: 'Sent' },
            { value: 'accepted', label: 'Accepted' },
            { value: 'declined', label: 'Declined' },
            { value: 'expired', label: 'Expired' },
          ],
        },
      ]}
      emptyTitle="No proposals yet"
      emptyDescription="Price a shortlisted yacht from an enquiry."
    />
  )
}
