import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { DepositRow } from '@/pages/Finance/SecurityDeposits/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<DepositRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived deposits"
      rows={rows}
      filters={filters}
      baseUrl="/finance/security-deposits"
      label={(row) => row.booking?.reference ?? 'Deposit'}
      subtitle={(row) => row.status}
    />
  )
}
