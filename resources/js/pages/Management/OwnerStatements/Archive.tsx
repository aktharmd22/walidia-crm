import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { OwnerStatementRow } from '@/pages/Management/OwnerStatements/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<OwnerStatementRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived owner statements"
      rows={rows}
      filters={filters}
      baseUrl="/management/owner-statements"
      label={(row) => row.yacht ?? 'Statement'}
      subtitle={(row) => row.reference}
    />
  )
}
