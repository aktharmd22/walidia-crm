import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { CrewRow } from '@/pages/Crew/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<CrewRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived crew"
      rows={rows}
      filters={filters}
      baseUrl="/crew"
      label={(row) => row.full_name}
      subtitle={(row) => row.role}
    />
  )
}
