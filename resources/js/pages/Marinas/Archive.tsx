import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { MarinaRow } from '@/pages/Marinas/Index'

export default function MarinasArchive({
  rows,
  filters,
}: {
  rows: Paginated<MarinaRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived marinas"
      rows={rows}
      filters={filters}
      baseUrl="/fleet/marinas"
      label={(row) => row.name}
      subtitle={(row) => row.country}
    />
  )
}
