import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { MaintenanceScheduleRow } from '@/pages/Management/MaintenanceSchedules/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<MaintenanceScheduleRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived maintenance schedules"
      rows={rows}
      filters={filters}
      baseUrl="/management/maintenance-schedules"
      label={(row) => row.title}
      subtitle={(row) => row.yacht}
    />
  )
}
