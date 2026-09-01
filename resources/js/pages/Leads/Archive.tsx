import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { LeadRow } from '@/pages/Leads/Index'

export default function LeadsArchive({
  rows,
  filters,
}: {
  rows: Paginated<LeadRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived leads"
      rows={rows}
      filters={filters}
      baseUrl="/leads"
      label={(row) => row.name}
    />
  )
}
