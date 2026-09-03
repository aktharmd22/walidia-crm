import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { ValuationRow } from '@/pages/Brokerage/Valuations/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<ValuationRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived valuations"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/valuations"
      label={(row) => row.yacht ?? 'Valuation'}
      subtitle={(row) => row.reference}
    />
  )
}
