import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { MessageTemplateRow } from '@/pages/Automation/MessageTemplates/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<MessageTemplateRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived message templates"
      rows={rows}
      filters={filters}
      baseUrl="/engine/message-templates"
      label={(row) => row.name}
      subtitle={(row) => row.key}
    />
  )
}
