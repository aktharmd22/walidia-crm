import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { InvoiceRow } from '@/pages/Finance/Invoices/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<InvoiceRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived invoices"
      rows={rows}
      filters={filters}
      baseUrl="/finance/invoices"
      label={(row) => row.reference ?? String(row.id)}
    />
  )
}
