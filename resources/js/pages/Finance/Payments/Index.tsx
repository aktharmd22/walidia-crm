import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Check, MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface PaymentRow {
  id: number
  reference: string | null
  method: string
  amount: string
  currency: string
  status: string
  status_tone: StatusTone
  is_cleared: boolean
  received_at: string | null
  cleared_at: string | null
  unallocated: number
  client?: { id: number; name: string } | null
  url: string
}

export default function PaymentsIndex({
  rows,
  filters,
  can,
}: {
  rows: Paginated<PaymentRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  const columns: ColumnDef<PaymentRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Payment',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.client?.name ?? row.original.reference ?? 'Payment'}
          subtitle={row.original.method.replace(/_/g, ' ')}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'amount',
      header: 'Amount',
      cell: ({ row }) => <Money amount={row.original.amount} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'received_at',
      header: 'Received',
      cell: ({ row }) => <DateText value={row.original.received_at} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'cleared_at',
      header: 'Cleared',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.cleared_at ? <DateText value={row.original.cleared_at} /> : <span className="text-ink-faint">Not cleared</span>,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          {!row.original.is_cleared && (
            <Button
              size="sm"
              variant="secondary"
              icon={<Check className="size-4" />}
              onClick={(event) => {
                event.stopPropagation()
                router.post(`/finance/payments/${row.original.id}/confirm-deposit`, {}, { preserveScroll: true })
              }}
            >
              Clear
            </Button>
          )}
          <DropdownMenu
            label="MENU"
            items={[{ key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) }]}
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
    <ResourceIndex<PaymentRow>
      title="Payments"
      description="Clearing a payment is what unlocks Operational Release, so it is a deliberate act rather than a side effect."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/finance/payments"
      createUrl="/finance/payments/create"
      createLabel="Record payment"
      searchPlaceholder="Search reference, client or gateway reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'pending', label: 'Pending' },
            { value: 'cleared', label: 'Cleared' },
            { value: 'failed', label: 'Failed' },
            { value: 'refunded', label: 'Refunded' },
          ],
        },
        {
          key: 'method',
          label: 'Method',
          options: [
            { value: '', label: 'Any' },
            { value: 'bank_transfer', label: 'Bank transfer' },
            { value: 'card', label: 'Card' },
            { value: 'cash', label: 'Cash' },
            { value: 'cheque', label: 'Cheque' },
            { value: 'link', label: 'Payment link' },
          ],
        },
      ]}
      emptyTitle="No payments recorded"
      emptyDescription="Record what arrives, then clear it once the bank confirms."
    />
  )
}
