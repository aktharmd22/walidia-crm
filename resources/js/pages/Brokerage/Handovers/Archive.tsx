import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { HandoverRow } from '@/pages/Brokerage/Handovers/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<HandoverRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived handovers"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/handovers"
      label={(row) => row.transaction ?? 'Handover'}
      subtitle={(row) => row.reference}
    />
  )
}
