import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { ListingRow } from '@/pages/Brokerage/Listings/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<ListingRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived listings"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/listings"
      label={(row) => row.yacht ?? 'Listing'}
      subtitle={(row) => row.reference}
    />
  )
}
