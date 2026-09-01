import { router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Download, MoreHorizontal, ShieldAlert } from 'lucide-react'
import { ResourceIndex } from '@/components/crud/ResourceIndex'
import { DropdownMenu } from '@/ui/Overlays'
import { DateText, IdentityCell } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated, StatusTone } from '@/types'

export interface DocumentRow {
  id: number
  reference: string | null
  title: string
  category: string
  original_name: string
  size_label: string | null
  version: number
  issued_on: string | null
  expires_on: string | null
  is_expired: boolean
  is_expiring: boolean
  expiry_tone: StatusTone
  visibility: string
  is_sensitive: boolean
  requires_signature: boolean
  signed_at: string | null
  status: string
  uploader?: string | null
  download_url: string
  url: string
}

export default function DocumentsIndex({
  rows,
  filters,
  can,
  heading,
}: {
  rows: Paginated<DocumentRow>
  filters: Record<string, unknown>
  can: { create?: boolean; export?: boolean }
  heading?: string
}) {
  const columns: ColumnDef<DocumentRow, unknown>[] = [
    {
      id: 'title',
      header: 'Document',
      cell: ({ row }) => (
        <span className="flex items-center gap-2">
          <IdentityCell name={row.original.title} subtitle={row.original.original_name} />
          {row.original.is_sensitive && (
            <ShieldAlert className="size-4 shrink-0 text-attention" aria-label="Sensitive" />
          )}
        </span>
      ),
      meta: { priority: 1 },
    },
    {
      id: 'category',
      header: 'Category',
      cell: ({ row }) => (
        <span className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">{row.original.category}</span>
      ),
      meta: { priority: 2 },
    },
    {
      id: 'expires_on',
      header: 'Expiry',
      cell: ({ row }) =>
        row.original.expires_on ? (
          <span className="flex items-center justify-end gap-2">
            <DateText value={row.original.expires_on} />
            {(row.original.is_expired || row.original.is_expiring) && (
              <StatusPill tone={row.original.expiry_tone}>
                {row.original.is_expired ? 'Expired' : 'Soon'}
              </StatusPill>
            )}
          </span>
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 1, align: 'end' },
    },
    {
      id: 'signature',
      header: 'Signature',
      enableSorting: false,
      cell: ({ row }) =>
        row.original.requires_signature ? (
          <StatusPill tone={row.original.signed_at ? 'success' : 'warning'}>
            {row.original.signed_at ? 'Signed' : 'Awaiting'}
          </StatusPill>
        ) : (
          <span className="text-ink-faint">—</span>
        ),
      meta: { priority: 3 },
    },
    {
      id: 'version',
      header: 'Version',
      enableSorting: false,
      cell: ({ row }) => <span className="numeric">v{row.original.version}</span>,
      meta: { priority: 3, align: 'end', numeric: true },
    },
    {
      id: 'actions',
      header: '',
      enableSorting: false,
      cell: ({ row }) => (
        <span className="flex items-center justify-end gap-1">
          <a
            href={row.original.download_url}
            onClick={(event) => event.stopPropagation()}
            className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink"
            aria-label={`Download ${row.original.title}`}
          >
            <Download className="size-4" aria-hidden />
          </a>
          <DropdownMenu
            label="MENU"
            items={[
              { key: 'view', label: 'Details', onSelect: () => router.visit(row.original.url) },
              { key: 'edit', label: 'Edit', onSelect: () => router.visit(`/documents/${row.original.id}/edit`) },
              {
                key: 'archive',
                label: 'Archive',
                destructive: true,
                separatorBefore: true,
                onSelect: () => router.delete(`/documents/${row.original.id}`),
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
    <ResourceIndex<DocumentRow>
      title={heading ?? 'Document Vault'}
      description="Everything is private by default. Downloads are authorised, logged, and served through a link that expires in five minutes."
      rows={rows}
      columns={columns}
      filters={filters}
      can={can}
      baseUrl="/documents"
      createUrl="/documents/create"
      createLabel="Upload"
      searchPlaceholder="Search title, file name or reference…"
      rowKey={(row) => row.id}
      rowUrl={(row) => row.url}
      bulkActions={[{ key: 'archive', label: 'Archive', destructive: true }]}
      filterFields={[
        {
          key: 'category',
          label: 'Category',
          options: [
            { value: '', label: 'Any' },
            { value: 'kyc', label: 'KYC' },
            { value: 'contract', label: 'Contract' },
            { value: 'certificate', label: 'Certificate' },
            { value: 'invoice', label: 'Invoice' },
            { value: 'proposal', label: 'Proposal' },
            { value: 'survey', label: 'Survey' },
            { value: 'statement', label: 'Statement' },
            { value: 'other', label: 'Other' },
          ],
        },
        {
          key: 'visibility',
          label: 'Visibility',
          options: [
            { value: '', label: 'Any' },
            { value: 'internal', label: 'Internal' },
            { value: 'client', label: 'Client' },
            { value: 'owner', label: 'Owner' },
            { value: 'portal', label: 'Portal' },
          ],
        },
      ]}
      mobileCard={(row) => (
        <div className="flex flex-col gap-2">
          <IdentityCell name={row.title} subtitle={row.category} />
          <div className="flex items-center justify-between gap-2">
            <span className="text-small text-ink-faint">{row.size_label ?? ''}</span>
            <DateText value={row.expires_on} className="text-small text-ink-soft" />
          </div>
        </div>
      )}
      emptyTitle="The vault is empty"
      emptyDescription="Contracts, KYC documents and certificates belong here rather than in an inbox."
    />
  )
}
