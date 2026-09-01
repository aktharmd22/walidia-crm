import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Check, MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { Button } from '@/ui/Button'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface TaskRow {
  id: number
  reference: string | null
  title: string
  type: string
  priority: string
  priority_tone: StatusTone
  status: string
  status_tone: StatusTone
  due_at: string | null
  is_overdue: boolean
  source: string
  assignee?: { id: number; name: string } | null
  subject?: { type: string; id: number; label: string | null }
  url: string
}

export default function TasksIndex({
  rows,
  filters,
  can,
  heading,
  users = [],
}: {
  rows: Paginated<TaskRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
  users?: { value: number; label: string }[]
}) {
  const columns: ColumnDef<TaskRow, unknown>[] = [
    {
      id: 'title',
      header: 'Task',
      cell: ({ row }) => (
        <span className="min-w-0">
          <span className="block truncate text-h3 text-ink">{row.original.title}</span>
          <span className="block truncate text-small text-ink-faint">
            {row.original.subject?.label ?? row.original.reference}
          </span>
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <StatusPill tone={row.original.status_tone}>
          {row.original.is_overdue ? 'Overdue' : row.original.status}
        </StatusPill>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'priority',
      header: 'Priority',
      cell: ({ row }) => <StatusPill tone={row.original.priority_tone}>{row.original.priority}</StatusPill>,
      meta: { priority: 2 },
    },
    {
      id: 'assignee',
      header: 'Owner',
      enableSorting: false,
      cell: ({ row }) => row.original.assignee?.name ?? <span className="text-ink-faint">Unassigned</span>,
      meta: { priority: 3 },
    },
    {
      id: 'due_at',
      header: 'Due',
      cell: ({ row }) => <DateText value={row.original.due_at} withTime />,
      meta: { priority: 1, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          {row.original.status === 'open' && (
            <Button
              size="sm"
              variant="secondary"
              icon={<Check className="size-4" />}
              onClick={(event) => {
                event.stopPropagation()
                router.post(`/tasks/${row.original.id}/complete`, {}, { preserveScroll: true })
              }}
            >
              Done
            </Button>
          )}
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/tasks/${row.original.id}/edit`) },
              {
                key: 'reopen',
                label: 'Reopen',
                disabled: row.original.status === 'open',
                onSelect: () => router.post(`/tasks/${row.original.id}/reopen`, {}, { preserveScroll: true }),
              },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/tasks/${row.original.id}`),
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
    <ResourceIndex<TaskRow>
      title={heading ?? 'My Tasks'}
      description="Next actions from the pipeline, plus anything a gate or a workflow raised for you."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/tasks"
      createUrl="/tasks/create"
      createLabel="New task"
      searchPlaceholder="Search tasks…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[
        { key: 'assign', label: 'Assign' },
        { key: 'archive', label: 'Archive', destructive: true },
      ]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'open', label: 'Open' },
            { value: 'done', label: 'Done' },
            { value: 'cancelled', label: 'Cancelled' },
          ],
        },
        {
          key: 'priority',
          label: 'Priority',
          options: [
            { value: '', label: 'Any' },
            { value: 'urgent', label: 'Urgent' },
            { value: 'high', label: 'High' },
            { value: 'normal', label: 'Normal' },
            { value: 'low', label: 'Low' },
          ],
        },
        { key: 'assigned_user_id', label: 'Owner', options: [{ value: '', label: 'Anyone' }, ...users] },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <span className="text-h3 text-ink">{row.title}</span>
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.is_overdue ? 'Overdue' : row.status}</StatusPill>
            <DateText value={row.due_at} className="text-small text-ink-soft" />
          </div>
        </div>
      )}
      emptyTitle="Nothing on your list"
      emptyDescription="Tasks assigned to you, and next actions raised by the pipeline, appear here."
    />
  )
}
