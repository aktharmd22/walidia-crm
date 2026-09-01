import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { TransactionRow } from '@/pages/Brokerage/Transactions/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<TransactionRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived transactions"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/transactions"
      label={(row) => row.listing ?? 'Transaction'}
      subtitle={(row) => row.reference}
    />
  )
}
