import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { TaskRow } from '@/pages/Tasks/Index'

export default function TasksArchive({
  rows,
  filters,
}: {
  rows: Paginated<TaskRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived tasks"
      rows={rows}
      filters={filters}
      baseUrl="/tasks"
      label={(row) => row.title}
    />
  )
}
