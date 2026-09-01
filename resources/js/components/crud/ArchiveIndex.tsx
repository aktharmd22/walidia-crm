import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { RotateCcw } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DateText, IdentityCell } from '@/ui/Primitives'
import type { Paginated } from '@/types'

export interface ArchivedRow {
  id: number
  deleted_at?: string | null
}

/**
 * "Delete" in this system means archive (D-008), so every module gets a real
 * restore screen rather than a dead end. One component, because the behaviour
 * must not vary by module.
 */
export function ArchiveIndex<T extends ArchivedRow>({
  title,
  rows,
  filters,
  baseUrl,
  label,
  subtitle,
}: {
  title: string
  rows: Paginated<T>
  filters: Record<string, unknown>
  baseUrl: string
  /** How to name the record in the first column. */
  label: (row: T) => string
  subtitle?: (row: T) => string | null
}) {
  const columns: ColumnDef<T, unknown>[] = [
    {
      id: 'label',
      header: 'Record',
      enableSorting: false,
      cell: ({ row }) => <IdentityCell name={label(row.original)} subtitle={subtitle?.(row.original) ?? null} />,
      meta: { priority: 1 },
    },
    {
      id: 'deleted_at',
      header: 'Archived',
      enableSorting: false,
      cell: ({ row }) => <DateText value={row.original.deleted_at ?? null} withTime />,
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
            onClick={() => router.post(`${baseUrl}/${row.original.id}/restore`)}
          >
            Restore
          </Button>
        </span>
      ),
      meta: { priority: 1, align: 'end' },
    },
  ]

  return (
    <ResourceIndex<T>
      title={title}
      description="Archived records keep their history, their documents and their timeline. Restoring puts them back exactly as they were."
      rows={rows}
      columns={columns}
      filters={filters}
      baseUrl={baseUrl}
      rowKey={(row) => row.id}
      emptyTitle="Nothing archived"
      emptyDescription="Archived records appear here and can be restored at any time."
    />
  )
}
