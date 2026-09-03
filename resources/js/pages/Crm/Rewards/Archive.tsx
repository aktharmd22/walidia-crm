import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { RewardRow } from '@/pages/Crm/Rewards/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<RewardRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived rewards"
      rows={rows}
      filters={filters}
      baseUrl="/crm/rewards"
      label={(row) => row.client ?? 'Reward'}
      subtitle={(row) => row.code}
    />
  )
}
