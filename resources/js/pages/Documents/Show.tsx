import { useRef } from 'react'
import { router } from '@inertiajs/react'
import { Download, ShieldAlert, UploadCloud } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { DocumentRow } from '@/pages/Documents/Index'

interface Version {
  id: number
  version: number
  note: string | null
  size: number
  uploader: string | null
  created_at: string | null
}

export default function DocumentShow({
  record,
  versions = [],
  exists,
  can,
}: {
  record: DocumentRow & { description: string | null; created_at: string | null }
  versions?: Version[]
  exists: boolean
  can: { update?: boolean; delete?: boolean }
}) {
  const fileInput = useRef<HTMLInputElement>(null)

  function uploadVersion(file: File) {
    router.post(`/documents/${record.id}/versions`, { file }, { forceFormData: true, preserveScroll: true })
  }

  return (
    <DetailShell
      title={record.title}
      subtitle={`${record.original_name}${record.size_label ? ` · ${record.size_label}` : ''}`}
      status={record.status}
      statusTone={record.status === 'active' ? 'success' : 'neutral'}
      editUrl={can.update ? `/documents/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/documents/${record.id}` : undefined}
      backUrl="/documents"
      actions={[
        <a key="download" href={record.download_url}>
          <Button variant="primary" icon={<Download className="size-4" />}>
            Download
          </Button>
        </a>,
        can.update ? (
          <span key="version">
            <input
              ref={fileInput}
              type="file"
              className="sr-only"
              onChange={(event) => {
                const file = event.target.files?.[0]
                if (file) uploadVersion(file)
              }}
            />
            <Button variant="secondary" icon={<UploadCloud className="size-4" />} onClick={() => fileInput.current?.click()}>
              New version
            </Button>
          </span>
        ) : null,
      ]}
      facts={[
        { label: 'Reference', value: <span className="numeric">{record.reference ?? '—'}</span> },
        { label: 'Category', value: record.category },
        { label: 'Version', value: <span className="numeric">v{record.version}</span> },
        { label: 'Issued', value: <DateText value={record.issued_on} /> },
        {
          label: 'Expires',
          value: (
            <span className="flex items-center justify-end gap-2">
              <DateText value={record.expires_on} />
              {(record.is_expired || record.is_expiring) && (
                <StatusPill tone={record.expiry_tone}>{record.is_expired ? 'Expired' : 'Soon'}</StatusPill>
              )}
            </span>
          ),
        },
        { label: 'Visibility', value: record.visibility },
        {
          label: 'Signature',
          value: record.requires_signature ? (
            <StatusPill tone={record.signed_at ? 'success' : 'warning'}>
              {record.signed_at ? 'Signed' : 'Awaiting'}
            </StatusPill>
          ) : (
            'Not required'
          ),
        },
        { label: 'Uploaded by', value: record.uploader ?? '—' },
        { label: 'Uploaded', value: <DateText value={record.created_at} withTime /> },
      ]}
    >
      {!exists && (
        <p className="flex items-center gap-2 rounded-card border border-danger bg-danger-bg px-4 py-3 text-small text-danger">
          <ShieldAlert className="size-4" aria-hidden />
          The file is missing from storage. The record is intact — re-upload it as a new version.
        </p>
      )}

      {record.is_sensitive && (
        <p className="flex items-center gap-2 rounded-card border border-attention bg-attention-bg px-4 py-3 text-small text-attention">
          <ShieldAlert className="size-4" aria-hidden />
          Marked sensitive. Only users with VIP access can download it, and every download is recorded.
        </p>
      )}

      {record.description && (
        <Card>
          <CardHeader>
            <CardTitle>Description</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.description}</p>
          </CardBody>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Version history</CardTitle>
        </CardHeader>
        {versions.length === 0 ? (
          <EmptyState
            title="Only the current version"
            description="Uploading a new version keeps the previous file — a superseded contract is still evidence."
          />
        ) : (
          <ul className="divide-y divide-line">
            {versions.map((version) => (
              <li key={version.id} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="min-w-0">
                  <span className="numeric text-h3 text-ink">v{version.version}</span>
                  {version.note && <span className="ms-2 text-body text-ink-soft">{version.note}</span>}
                </span>
                <span className="text-small text-ink-faint">
                  {version.uploader ?? 'Unknown'} · <DateText value={version.created_at} />
                </span>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </DetailShell>
  )
}
