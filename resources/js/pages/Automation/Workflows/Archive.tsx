import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { WorkflowRow } from '@/pages/Automation/Workflows/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<WorkflowRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived automation rules"
      rows={rows}
      filters={filters}
      baseUrl="/engine/workflows"
      label={(row) => row.name}
      subtitle={(row) => row.key}
    />
  )
}
