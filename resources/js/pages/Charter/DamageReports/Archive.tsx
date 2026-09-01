import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { DamageRow } from '@/pages/Charter/DamageReports/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<DamageRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived damage reports"
      rows={rows}
      filters={filters}
      baseUrl="/charter/damage-reports"
      label={(row) => row.reference ?? 'Damage'}
      subtitle={(row) => row.booking?.reference ?? null}
    />
  )
}
