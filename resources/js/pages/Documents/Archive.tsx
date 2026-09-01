import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { DocumentRow } from '@/pages/Documents/Index'

export default function DocumentsArchive({
  rows,
  filters,
}: {
  rows: Paginated<DocumentRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived documents"
      rows={rows}
      filters={filters}
      baseUrl="/documents"
      label={(row) => row.title}
      subtitle={(row) => row.category}
    />
  )
}
