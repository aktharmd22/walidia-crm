import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MessageSquare, MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface LeadRow {
  id: number
  reference: string
  name: string
  email: string | null
  mobile: string | null
  business_line: string
  status: string
  status_tone: StatusTone
  source?: string | null
  assignee?: { id: number; name: string } | null
  sla_due_at: string | null
  is_overdue: boolean
  created_at: string | null
  url: string
}

export default function LeadsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<LeadRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean; import?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<LeadRow, unknown>[] = [
    {
      id: 'name',
      header: 'Lead',
      cell: ({ row }) => <IdentityCell name={row.original.name} subtitle={row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>
          {row.original.is_overdue && <StatusPill tone="danger">SLA missed</StatusPill>}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'business_line',
      header: 'Line',
      cell: ({ row }) => <span className="capitalize">{row.original.business_line}</span>,
      meta: { priority: 2 },
    },
    {
      id: 'source',
      header: 'Source',
      enableSorting: false,
      cell: ({ row }) => row.original.source ?? '—',
      meta: { priority: 3 },
    },
    {
      id: 'assignee',
      header: 'Owner',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.assignee?.name ?? <span className="text-ink-faint">Unassigned</span>,
      meta: { priority: 2 },
    },
    {
      id: 'sla_due_at',
      header: 'Respond by',
      cell: ({ row }) => <DateText value={row.original.sla_due_at} withTime />,
      meta: { priority: 3, align: 'end' },
    },
    {
      id: 'created_at',
      header: 'Received',
      cell: ({ row }) => <DateText value={row.original.created_at} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          {row.original.mobile && (
            <a
              href={`https://wa.me/${row.original.mobile.replace(/[^0-9]/g, '')}`}
              target="_blank"
              rel="noreferrer"
              onClick={(event) => event.stopPropagation()}
              className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
              aria-label="Message on WhatsApp"
            >
              <MessageSquare className="size-4" aria-hidden />
            </a>
          )}
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/leads/${row.original.id}/edit`) },
              {
                key: 'convert',
                label: 'Convert to client',
                onSelect: () => router.post(`/leads/${row.original.id}/convert`, { create_deal: true }),
              },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/leads/${row.original.id}`),
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
    <ResourceIndex<LeadRow>
      title={heading ?? 'Leads'}
      description="Every enquiry starts here. The response clock starts when the lead lands, not when someone opens it."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/leads"
      createUrl="/leads/create"
      createLabel="New lead"
      searchPlaceholder="Search name, email, mobile or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[
        { key: 'assign', label: 'Assign' },
        { key: 'archive', label: 'Archive', destructive: true },
      ]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'new', label: 'New' },
            { value: 'contacted', label: 'Contacted' },
            { value: 'qualified', label: 'Qualified' },
            { value: 'registered', label: 'Registered' },
            { value: 'unqualified', label: 'Unqualified' },
            { value: 'duplicate', label: 'Duplicate' },
          ],
        },
        {
          key: 'business_line',
          label: 'Business line',
          options: [
            { value: '', label: 'Any' },
            { value: 'charter', label: 'Charter' },
            { value: 'brokerage', label: 'Brokerage' },
            { value: 'management', label: 'Management' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.name} subtitle={row.reference} />
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.status}</StatusPill>
            <span className="numeric text-small text-ink-soft">{row.mobile ?? '—'}</span>
          </div>
        </div>
      )}
      emptyTitle="No leads here"
      emptyDescription="Website enquiries, WhatsApp messages and referrals all land in this list."
    />
  )
}
