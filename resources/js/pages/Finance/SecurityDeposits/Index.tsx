import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface DepositRow {
  id: number
  amount: string
  currency: string
  method: string
  status: string
  status_tone: StatusTone
  is_held: boolean
  collected_at: string | null
  released_amount: string | null
  released_at: string | null
  booking?: { id: number; reference: string; client: string | null; url: string } | null
  url: string
}

export default function SecurityDepositsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<DepositRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<DepositRow, unknown>[] = [
    {
      id: 'booking',
      header: 'Charter',
      enableSorting: false,
      cell: ({ row }) => (
        <IdentityCell name={row.original.booking?.client ?? 'Deposit'} subtitle={row.original.booking?.reference} />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status.replace('_', ' ')}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'amount',
      header: 'Held',
      cell: ({ row }) => <Money amount={row.original.amount} currency={row.original.currency} />,
      meta: { priority: 1, align: 'end', numeric: true },
    },
    {
      id: 'method',
      header: 'Method',
      enableSorting: false,
      cell: ({ row }) => row.original.method.replace(/_/g, ' '),
      meta: { priority: 3 },
    },
    {
      id: 'collected_at',
      header: 'Collected',
      cell: ({ row }) => <DateText value={row.original.collected_at} />,
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/finance/security-deposits/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/finance/security-deposits/${row.original.id}`),
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
    <ResourceIndex<DepositRow>
      title={heading ?? 'Security deposits'}
      description="Held against damage, released only once the inspection is closed."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/finance/security-deposits"
      createUrl="/finance/security-deposits/create"
      createLabel="Record deposit"
      searchPlaceholder="Search booking reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'held', label: 'Held' },
            { value: 'partially_released', label: 'Partially released' },
            { value: 'released', label: 'Released' },
            { value: 'forfeited', label: 'Forfeited' },
          ],
        },
      ]}
      emptyTitle="No deposits held"
      emptyDescription="Deposits taken against a charter appear here until they are released."
    />
  )
}
