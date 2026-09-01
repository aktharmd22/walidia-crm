import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { DealRow } from '@/pages/Deals/Index'

export default function DealsArchive({
  rows,
  filters,
}: {
  rows: Paginated<DealRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived deals"
      rows={rows}
      filters={filters}
      baseUrl="/deals"
      label={(row) => row.title}
      subtitle={(row) => row.client?.name ?? null}
    />
  )
}
