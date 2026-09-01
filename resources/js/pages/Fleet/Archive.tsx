import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { YachtRow } from '@/pages/Fleet/Index'

export default function FleetArchive({
  rows,
  filters,
}: {
  rows: Paginated<YachtRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived yachts"
      rows={rows}
      filters={filters}
      baseUrl="/fleet/yachts"
      label={(row) => row.name}
      subtitle={(row) => [row.builder, row.model].filter(Boolean).join(' ') || null}
    />
  )
}
