import { ArchiveIndex } from '@/components/crud/ArchiveIndex'
import type { Paginated } from '@/types'
import type { SurveyRow } from '@/pages/Brokerage/Surveys/Index'

export default function Archive({
  rows,
  filters,
}: {
  rows: Paginated<SurveyRow & { deleted_at?: string | null }>
  filters: Record<string, unknown>
}) {
  return (
    <ArchiveIndex
      title="Archived surveys and sea trials"
      rows={rows}
      filters={filters}
      baseUrl="/brokerage/surveys"
      label={(row) => row.type ?? 'Surveys and sea trials'}
      subtitle={(row) => row.reference}
    />
  )
}
