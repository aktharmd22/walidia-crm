import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { LayoutGrid, MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface DealRow {
  id: number
  reference: string | null
  title: string
  value?: string | null
  currency: string
  status: string
  days_in_stage: number
  expected_close_date: string | null
  stage?: { id: number; name: string; tone: StatusTone } | null
  pipeline?: { id: number; key: string; name: string } | null
  client?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  url: string
}

export default function DealsIndex({
  rows,
  filters,
  can,
  pipelines = [],
}: {
  rows: Paginated<DealRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  pipelines?: { id: number; key: string; name: string }[]
}) {
  const columns: ColumnDef<DealRow, unknown>[] = [
    {
      id: 'title',
      header: 'Deal',
      cell: ({ row }) => <IdentityCell name={row.original.title} subtitle={row.original.client?.name ?? row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'stage',
      header: 'Stage',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.stage ? (
          <StatusPill tone={row.original.stage.tone}>{row.original.stage.name}</StatusPill>
        ) : (
          '—'
        ),
      meta: { priority: 1 },
    },
    {
      id: 'pipeline',
      header: 'Pipeline',
      enableSorting: false,
      cell: ({ row }) => row.original.pipeline?.name ?? '—',
      meta: { priority: 3 },
    },
    {
      id: 'value',
      header: 'Value',
      cell: ({ row }) =>
        row.original.value !== undefined && row.original.value !== null ? (
          <Money amount={row.original.value} currency={row.original.currency} />
        ) : (
          <span className="text-ink-faint">Restricted</span>
        ),
      meta: { priority: 2, align: 'end', numeric: true },
    },
    {
      id: 'stage_entered_at',
      header: 'In stage',
      cell: ({ row }) => (
        <span className="numeric">
          <Num value={row.original.days_in_stage} /> d
        </span>
      ),
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'expected_close_date',
      header: 'Close',
      cell: ({ row }) => <DateText value={row.original.expected_close_date} />,
      meta: { priority: 2, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex justify-end">
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/deals/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/deals/${row.original.id}`),
              },
            ]}
            trigger={
              <button
                type="button"
                onClick={(event) => event.stopPropagation()}
                className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
                aria-label="More actions"
              >
                <MoreHorizontal className="size-4" aria-hidden />
              </button>
            }
          />
        </span>
      ),
      meta: { priority: 1, align: 'end' },
    },
  ]

  return (
    <ResourceIndex<DealRow>
      title="Deals"
      description="The table view of the pipeline. Same records, different reading."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/deals"
      createUrl="/deals/create"
      createLabel="New deal"
      searchPlaceholder="Search deal title or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[
        { key: 'assign', label: 'Assign' },
        { key: 'archive', label: 'Archive', destructive: true },
      ]}
      filterFields={[
        {
          key: 'pipeline_id',
          label: 'Pipeline',
          options: [{ value: '', label: 'Any' }, ...pipelines.map((p) => ({ value: p.id, label: p.name }))],
        },
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'open', label: 'Open' },
            { value: 'won', label: 'Won' },
            { value: 'lost', label: 'Lost' },
          ],
        },
      ]}
    >
      <div className="flex justify-end">
        <Button variant="secondary" icon={<LayoutGrid className="size-4" />} onClick={() => router.visit('/deals/board')}>
          Board view
        </Button>
      </div>
    </ResourceIndex>
  )
}
