import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { ViewingRow } from '@/pages/Brokerage/Viewings/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<ViewingRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived viewings"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/viewings"
      label={(row) => row.client ?? 'Viewing'}
      subtitle={(row) => row.listing}
    />
  )
}
