import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { OfferRow } from '@/pages/Brokerage/Offers/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<OfferRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived offers"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/offers"
      label={(row) => row.client ?? 'Offer'}
      subtitle={(row) => row.listing}
    />
  )
}
