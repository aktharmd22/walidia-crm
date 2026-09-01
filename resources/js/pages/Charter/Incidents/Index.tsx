import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface IncidentRow {
  id: number
  reference: string | null
  type: string
  severity: string
  severity_tone: StatusTone
  occurred_at: string
  description: string
  status: string
  status_tone: StatusTone
  booking?: { id: number; reference: string; url: string } | null
  yacht?: string | null
  url: string
}

export default function IncidentsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<IncidentRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<IncidentRow, unknown>[] = [
    {
      id: 'reference',
      header: 'Incident',
      cell: ({ row }) => <IdentityCell name={row.original.type.replace(/_/g, ' ')} subtitle={row.original.reference} />,
      meta: { priority: 1 },
    },
    {
      id: 'severity',
      header: 'Severity',
      cell: ({ row }) => <StatusPill tone={row.original.severity_tone}>{row.original.severity}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>,
      meta: { priority: 1 },
    },
    {
      id: 'yacht',
      header: 'Yacht',
      enableSorting: false,
      cell: ({ row }) => row.original.yacht ?? '—',
      meta: { priority: 3 },
    },
    {
      id: 'occurred_at',
      header: 'Occurred',
      cell: ({ row }) => <DateText value={row.original.occurred_at} withTime />,
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/charter/incidents/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/charter/incidents/${row.original.id}`),
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
    <ResourceIndex<IncidentRow>
      title={heading ?? 'Incidents'}
      description="What went wrong, how badly, and whether it is closed."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/charter/incidents"
      createUrl="/charter/incidents/create"
      createLabel="Report incident"
      searchPlaceholder="Search reference or type…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'open', label: 'Open' },
            { value: 'investigating', label: 'Investigating' },
            { value: 'closed', label: 'Closed' },
          ],
        },
        {
          key: 'severity',
          label: 'Severity',
          options: [
            { value: '', label: 'Any' },
            { value: 'minor', label: 'Minor' },
            { value: 'moderate', label: 'Moderate' },
            { value: 'major', label: 'Major' },
            { value: 'critical', label: 'Critical' },
          ],
        },
      ]}
      emptyTitle="No incidents recorded"
      emptyDescription="Anything that went wrong on a charter belongs here, with what was done about it."
    />
  )
}
