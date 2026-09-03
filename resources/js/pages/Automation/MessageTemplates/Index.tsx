import type { ColumnDef } from '@tanstack/react-table'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface MessageTemplateRow {
  id: number
  key: string
  name: string
  channel: string
  subject_en: string | null
  body_en: string
  subject_ar: string | null
  body_ar: string | null
  merge_fields: string[] | null
  category: string
  is_active: boolean
  status: string
  status_tone: StatusTone
  url: string
}

const columns: ColumnDef<MessageTemplateRow>[] = [
  {
    id: 'name',
    header: 'Template',
    cell: ({ row }) => <IdentityCell name={row.original.name} subtitle={row.original.key} />,
  },
  {
    id: 'channel',
    header: 'Channel',
    cell: ({ row }) => <StatusPill tone="info">{row.original.channel}</StatusPill>,
  },
  {
    id: 'category',
    header: 'Audience',
    cell: ({ row }) => <span className="text-small text-ink-soft">{row.original.category}</span>,
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
  },
]

export default function Index({
  rows,
  filters,
  can,
}: {
  rows: Paginated<MessageTemplateRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
}) {
  return (
    <ResourceIndex
      title="Message templates"
      description="What the system says, in both languages, with the merge fields it expects."
      rows={rows}
      filters={filters}
      columns={columns}
      baseUrl="/engine/message-templates"
      can={can}
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
    />
  )
}
