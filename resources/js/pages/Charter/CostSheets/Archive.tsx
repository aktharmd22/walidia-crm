import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'

interface Row {
  id: number
  reference: string
  deleted_at?: string | null
}

export default function CostSheetsArchive({
  rows,
  filters,
}: {
  rows: Paginated<Row>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived cost sheets"
      rows={rows}
      filters={filters}
      baseUrl="/charter/cost-sheets"
      label={(row) => row.reference}
    />
  )
}
