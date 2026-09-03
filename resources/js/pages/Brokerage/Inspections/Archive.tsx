import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { InspectionRow } from '@/pages/Brokerage/Inspections/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<InspectionRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived inspections"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/inspections"
      label={(row) => row.yacht ?? 'Inspection'}
      subtitle={(row) => row.reference}
    />
  )
}
