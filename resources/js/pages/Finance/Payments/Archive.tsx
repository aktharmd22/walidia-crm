import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { PaymentRow } from '@/pages/Finance/Payments/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<PaymentRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived payments"
      rows={rows}
      filters={filters}
      baseUrl="/finance/payments"
      label={(row) => row.reference ?? String(row.id)}
    />
  )
}
