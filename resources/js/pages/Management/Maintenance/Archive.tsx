import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { MaintenanceRow } from '@/pages/Management/Maintenance/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<MaintenanceRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived maintenance jobs"
      rows={rows}
      filters={filters}
      baseUrl="/management/maintenance"
      label={(row) => row.title}
      subtitle={(row) => row.yacht}
    />
  )
}
