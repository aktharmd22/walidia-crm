import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { CertificateRow } from '@/pages/Management/Certificates/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<CertificateRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived certificates"
      rows={rows}
      filters={filters}
      baseUrl="/management/certificates"
      label={(row) => row.name}
      subtitle={(row) => row.yacht}
    />
  )
}
