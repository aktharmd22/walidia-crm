import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { VendorRow } from '@/pages/Vendors/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<VendorRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived vendors"
      rows={rows}
      filters={filters}
      baseUrl="/vendors"
      label={(row) => row.display_name}
      subtitle={(row) => row.category ?? null}
    />
  )
}
