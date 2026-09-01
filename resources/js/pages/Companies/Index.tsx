import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface CompanyRow {
  id: number
  reference: string | null
  legal_name: string
  trade_name: string | null
  display_name: string
  type: string
  email: string | null
  phone: string | null
  city: string | null
  country: string | null
  licence_expiry: string | null
  licence_expiring: boolean
  status: string
  status_tone: StatusTone
  clients_count?: number
  url: string
}

const typeLabels: Record<string, string> = {
  corporate: 'Corporate',
  dmc: 'DMC',
  concierge: 'Concierge',
  charter_partner: 'Charter partner',
  broker: 'Broker',
  supplier: 'Supplier',
}

export default function CompaniesIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<CompanyRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean; import?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<CompanyRow, unknown>[] = [
    {
      id: 'legal_name',
      header: 'Company',
      cell: ({ row }) => (
        <IdentityCell name={row.original.display_name} subtitle={row.original.reference ?? row.original.legal_name} />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'type',
      header: 'Type',
      cell: ({ row }) => (
        <span className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">
          {typeLabels[row.original.type] ?? row.original.type}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <StatusPill tone={row.original.status_tone}>{row.original.status}</StatusPill>
          {row.original.licence_expiring && <StatusPill tone="warning">Licence expiring</StatusPill>}
        </span>
      ),
      meta: { priority: 2 },
    },
    {
      id: 'clients_count',
      header: 'Clients',
      enableSorting: false,
      cell: ({ row }) => <Num value={row.original.clients_count ?? 0} />,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'city',
      header: 'Location',
      enableSorting: false,
      cell: ({ row }) => [row.original.city, row.original.country].filter(Boolean).join(', ') || '—',
      meta: { priority: 3 },
    },
    {
      id: 'licence_expiry',
      header: 'Licence expiry',
      enableSorting: false,
      cell: ({ row }) => <DateText value={row.original.licence_expiry} />,
      meta: { priority: 3, align: 'end' },
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
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/companies/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/companies/${row.original.id}`),
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
    <ResourceIndex<CompanyRow>
      title={heading ?? 'Companies'}
      description="Corporate clients, DMCs, concierges, charter partners and co-brokers."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/companies"
      createUrl="/companies/create"
      createLabel="New company"
      searchPlaceholder="Search company name, email or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'type',
          label: 'Type',
          options: [
            { value: '', label: 'Any' },
            ...Object.entries(typeLabels).map(([value, label]) => ({ value, label })),
          ],
        },
        {
          key: 'status',
          label: 'Status',
          options: [
            { value: '', label: 'Any' },
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
            { value: 'blacklisted', label: 'Blacklisted' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.display_name} subtitle={typeLabels[row.type] ?? row.type} />
          <StatusPill tone={row.status_tone}>{row.status}</StatusPill>
        </div>
      )}
      emptyTitle="No companies yet"
      emptyDescription="Add the DMCs, concierges and corporate clients you work with, so referrals and commissions have somewhere to land."
    />
  )
}
