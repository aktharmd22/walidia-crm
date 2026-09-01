import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface InvoiceRow {
  id: number
  reference: string | null
  type: string
  status: string
  status_tone: StatusTone
  is_overdue: boolean
  issue_date: string | null
  due_date: string | null
  currency: string
  total: string
  amount_due: string
  tax_treatment: string
  client?: { id: number; name: string } | null
  url: string
}

export default function InvoicesIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<InvoiceRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<InvoiceRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Invoice',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.reference ?? 'Draft'}
          subtitle={row.original.client?.name ?? row.original.type.replace('_', ' ')}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <StatusPill tone={row.original.status_tone}>
          {row.original.is_overdue ? 'Overdue' : row.original.status.replace('_', ' ')}
        </StatusPill>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'tax_treatment',
      header: 'VAT',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">
          {row.original.tax_treatment.replace(/_/g, ' ')}
        </span>
      ),
      meta: { priority: 3 },
    },
    {
      id: 'total',
      header: 'Total',
      cell: ({ row }) => <Money amount={row.original.total} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'amount_due',
      header: 'Outstanding',
      enableSorting: false,
      cell: ({ row }) => <Money amount={row.original.amount_due} currency={row.original.currency} />,
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'due_date',
      header: 'Due',
      cell: ({ row }) => <DateText value={row.original.due_date} />,
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/finance/invoices/${row.original.id}/edit`) },
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
    <ResourceIndex<InvoiceRow>
      title={heading ?? 'Invoices'}
      description="Issued invoices are never edited — they are voided and credited, so the numbering stays gapless."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/finance/invoices"
      createUrl="/finance/invoices/create"
      createLabel="New invoice"
      searchPlaceholder="Search reference or client…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'draft', label: 'Draft' },
            { value: 'issued', label: 'Issued' },
            { value: 'partially_paid', label: 'Partially paid' },
            { value: 'paid', label: 'Paid' },
            { value: 'overdue', label: 'Overdue' },
            { value: 'void', label: 'Void' },
            { value: 'credited', label: 'Credited' },
          ],
        },
      ]}
      emptyTitle="No invoices yet"
      emptyDescription="Raise one from a cost sheet, or create a draft directly."
    />
  )
}
