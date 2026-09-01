import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { ProposalRow } from '@/pages/Charter/Proposals/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<ProposalRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived proposals"
      rows={rows}
      filters={filters}
      baseUrl="/charter/proposals"
      label={(row) => row.reference ?? String(row.id)}
    />
  )
}
