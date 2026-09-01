import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Check } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated } from '@/types'
import type { ClientRow } from '@/pages/Clients/Index'

/**
 * New clients awaiting KYC/AML clearance before they can transact (Q27).
 */
export default function ClientsApprovalQueue({
  rows,
  filters,
  can,
}: {
  rows: Paginated<ClientRow>
  filters: Record<string, unknown>
  can: { approve?: boolean }
}) {
  const columns: ColumnDef<ClientRow, unknown>[] = [
    {
      id: 'full_name',
      header: 'Client',
      cell: ({ row }) => <IdentityCell name={row.original.full_name} subtitle={row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'kyc_status',
      header: 'KYC',
      cell: ({ row }) => <StatusPill tone={row.original.kyc_tone}>{row.original.kyc_status.replace('_', ' ')}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'assignee',
      header: 'Requested by',
      enableSorting: false,
      cell: ({ row }) => row.original.assignee?.name ?? '—',
      meta: { priority: 2 },
    },
    {
      id: 'created_at',
      header: 'Waiting since',
      cell: ({ row }) => <DateText value={row.original.created_at} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex justify-end">
          {can.approve && (
            <Button
              size="sm"
              variant="primary"
              icon={<Check className="size-4" />}
              onClick={() => router.post(`/clients/${row.original.id}/approve`)}
            >
              Approve
            </Button>
          )}
        </span>
      ),
      meta: { priority: 1, align: 'end' },
    },
  ]

  return (
    <ResourceIndex
      title="Approval queue"
      description="Clients cannot transact until KYC and AML clearance are recorded."
      rows={rows}
      columns={columns}
      filters={filters}
      baseUrl="/clients"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      emptyTitle="Nothing waiting"
      emptyDescription="New client records needing clearance appear here."
    />
  )
}
