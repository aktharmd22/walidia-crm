import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { EnquiryRow } from '@/pages/Charter/Enquiries/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<EnquiryRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived enquiries"
      rows={rows}
      filters={filters}
      baseUrl="/charter/enquiries"
      label={(row) => row.reference ?? String(row.id)}
    />
  )
}
