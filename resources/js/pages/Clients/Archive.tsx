import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { RotateCcw } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DateText, IdentityCell } from '@/ui/Primitives'
import type { Paginated } from '@/types'
import type { ClientRow } from '@/pages/Clients/Index'

/** Archived clients. Nothing is ever hard-deleted (D-008), so this is a real screen. */
export default function ClientsArchive({
  rows,
  filters,
}: {
  rows: Paginated<ClientRow & { deleted_at: string | null }>
  filters: Record<string, unknown>
}) {
  const columns: ColumnDef<ClientRow & { deleted_at: string | null }, unknown>[] = [
    {
      id: 'full_name',
      header: 'Client',
      cell: ({ row }) => <IdentityCell name={row.original.full_name} subtitle={row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'deleted_at',
      header: 'Archived',
      cell: ({ row }) => <DateText value={row.original.deleted_at} withTime />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex justify-end">
          <Button
            size="sm"
            variant="secondary"
            icon={<RotateCcw className="size-4" />}
            onClick={() => router.post(`/clients/${row.original.id}/restore`)}
          >
            Restore
          </Button>
        </span>
      ),
      meta: { priority: 1, align: 'end' },
    },
  ]

  return (
    <ResourceIndex
      title="Archived clients"
      description="Archived records keep their history and can be restored."
      rows={rows}
      columns={columns}
      filters={filters}
      baseUrl="/clients"
      rowKey={(row) => row.id}
      emptyTitle="Nothing archived"
      emptyDescription="Archived clients appear here and can be restored at any time."
    />
  )
}
