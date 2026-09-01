import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { MoreHorizontal, Phone, ShieldCheck } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface ClientRow {
  id: number
  reference: string
  full_name: string
  email: string | null
  mobile: string | null
  client_type: string[]
  vip_level: string
  status: string
  status_tone: StatusTone
  kyc_status: string
  kyc_tone: StatusTone
  company?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  created_at: string | null
  url: string
}

const typeLabels: Record<string, string> = {
  charter_guest: 'Charter guest',
  buyer: 'Buyer',
  seller: 'Seller',
  owner: 'Owner',
  partner: 'Partner',
}

export default function ClientsIndex({
  rows,
  filters,
  can,
  heading,
  scope,
}: {
  rows: Paginated<ClientRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean; import?: boolean }
  heading?: string
  scope?: string
}) {
  const columns: ColumnDef<ClientRow, unknown>[] = [
    {
      id: 'full_name',
      header: 'Client',
      cell: ({ row }) => (
        <IdentityCell
          name={row.original.full_name}
          subtitle={row.original.company?.name ?? row.original.reference}
        />
      ),
      meta: { priority: 1 },
    },
    {
      id: 'client_type',
      header: 'Type',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex flex-wrap gap-1">
          {row.original.client_type.map((type) => (
            <span key={type} className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">
              {typeLabels[type] ?? type}
            </span>
          ))}
        </span>
      ),
      meta: { priority: 2 },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <StatusPill tone={row.original.status_tone}>{row.original.status.replace('_', ' ')}</StatusPill>
          {row.original.vip_level !== 'none' && (
            <StatusPill tone="attention" dot={false}>
              {row.original.vip_level.toUpperCase()}
            </StatusPill>
          )}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'kyc_status',
      header: 'KYC',
      cell: ({ row }) => <StatusPill tone={row.original.kyc_tone}>{row.original.kyc_status.replace('_', ' ')}</StatusPill>,
      meta: { priority: 2 },
    },
    {
      id: 'mobile',
      header: 'Contact',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex flex-col">
          <span className="numeric text-body text-ink">{row.original.mobile ?? '—'}</span>
          <span className="text-small text-ink-faint">{row.original.email ?? ''}</span>
        </span>
      ),
      meta: { priority: 3 },
    },
    {
      id: 'created_at',
      header: 'Added',
      cell: ({ row }) => <DateText value={row.original.created_at} />,
      meta: { priority: 3, align: 'end' },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          {row.original.mobile && (
            <a
              href={`tel:${row.original.mobile}`}
              onClick={(event) => event.stopPropagation()}
              className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
              aria-label={`Call ${row.original.full_name}`}
            >
              <Phone className="size-4" aria-hidden />
            </a>
          )}
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/clients/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/clients/${row.original.id}`),
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
    <ResourceIndex<ClientRow>
      title={heading ?? 'Clients'}
      description={
        scope === 'vip'
          ? 'Protected records. Every time identity or dietary data is opened here, it is logged against your name.'
          : 'One record per person, however many ways they do business with Walidia.'
      }
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/clients"
      createUrl="/clients/create"
      createLabel="New client"
      searchPlaceholder="Search name, email, mobile or reference…"
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
            { value: 'active', label: 'Active' },
            { value: 'dormant', label: 'Dormant' },
            { value: 'pending_approval', label: 'Pending approval' },
            { value: 'blacklisted', label: 'Blacklisted' },
          ],
        },
        {
          key: 'kyc_status',
          label: 'KYC',
          options: [
            { value: '', label: 'Any' },
            { value: 'not_started', label: 'Not started' },
            { value: 'pending', label: 'Pending' },
            { value: 'verified', label: 'Verified' },
            { value: 'rejected', label: 'Rejected' },
            { value: 'expired', label: 'Expired' },
          ],
        },
        {
          key: 'vip_level',
          label: 'VIP level',
          options: [
            { value: '', label: 'Any' },
            { value: 'none', label: 'Standard' },
            { value: 'vip', label: 'VIP' },
            { value: 'uhnw', label: 'UHNW' },
            { value: 'protected', label: 'Protected' },
          ],
        },
        {
          key: 'type',
          label: 'Client type',
          options: [
            { value: '', label: 'Any' },
            { value: 'charter_guest', label: 'Charter guest' },
            { value: 'buyer', label: 'Buyer' },
            { value: 'seller', label: 'Seller' },
            { value: 'owner', label: 'Owner' },
            { value: 'partner', label: 'Partner' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.full_name} subtitle={row.company?.name ?? row.reference} />
          <div className="flex items-center justify-between gap-2">
            <StatusPill tone={row.status_tone}>{row.status.replace('_', ' ')}</StatusPill>
            <span className="numeric text-small text-ink-soft">{row.mobile ?? '—'}</span>
          </div>
        </div>
      )}
      emptyTitle="No clients yet"
      emptyDescription="Convert a lead, or add a client directly. One record can be a charter guest, a buyer and an owner at once."
    >
      {scope === 'vip' && (
        <p className="flex items-center gap-2 rounded-card border border-attention bg-attention-bg px-4 py-3 text-small text-attention">
          <ShieldCheck className="size-4" aria-hidden />
          Access to these records is recorded in the audit log.
        </p>
      )}
    </ResourceIndex>
  )
}
