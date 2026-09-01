import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { NdaRow } from '@/pages/Brokerage/Ndas/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<NdaRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived ndas"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/ndas"
      label={(row) => row.client ?? 'NDA'}
      subtitle={(row) => row.reference}
    />
  )
}
