import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { PayoutRow } from '@/pages/Finance/Payouts/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<PayoutRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived payouts"
      rows={rows}
      filters={filters}
      baseUrl="/finance/payouts"
      label={(row) => row.payee_name}
      subtitle={(row) => row.reference}
    />
  )
}
