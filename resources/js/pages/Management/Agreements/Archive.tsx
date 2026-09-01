import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { AgreementRow } from '@/pages/Management/Agreements/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<AgreementRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived management agreements"
      rows={rows}
      filters={filters}
      baseUrl="/management/agreements"
      label={(row) => row.yacht ?? 'Agreement'}
      subtitle={(row) => row.reference}
    />
  )
}
