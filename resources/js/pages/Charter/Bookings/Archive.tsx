import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { BookingRow } from '@/pages/Charter/Bookings/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<BookingRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived bookings"
      rows={rows}
      filters={filters}
      baseUrl="/charter/bookings"
      label={(row) => row.reference ?? String(row.id)}
    />
  )
}
