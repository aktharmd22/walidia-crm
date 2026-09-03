import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { JourneyRow } from '@/pages/Crm/Journeys/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<JourneyRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived journeys"
      rows={rows}
      filters={filters}
      baseUrl="/crm/journeys"
      label={(row) => row.client ?? 'Journey'}
      subtitle={(row) => row.type}
    />
  )
}
