import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { BuyerRequirementRow } from '@/pages/Brokerage/BuyerRequirements/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<BuyerRequirementRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived buyer requirements"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/buyer-requirements"
      label={(row) => row.client ?? 'Requirement'}
      subtitle={(row) => row.reference}
    />
  )
}
