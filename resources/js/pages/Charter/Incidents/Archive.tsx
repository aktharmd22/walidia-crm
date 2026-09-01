import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { IncidentRow } from '@/pages/Charter/Incidents/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<IncidentRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived incidents"
      rows={rows}
      filters={filters}
      baseUrl="/charter/incidents"
      label={(row) => row.reference ?? 'Incident'}
      subtitle={(row) => row.type}
    />
  )
}
