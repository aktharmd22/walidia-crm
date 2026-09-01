import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { CompanyRow } from '@/pages/Companies/Index'

export default function CompaniesArchive({
  rows,
  filters,
}: {
  rows: Paginated<CompanyRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived companies"
      rows={rows}
      filters={filters}
      baseUrl="/companies"
      label={(row) => row.display_name}
    />
  )
}
