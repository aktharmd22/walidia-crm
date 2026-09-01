import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { FileText, Plus } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardHeader, CardTitle, DateText, EmptyState, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Drawer } from '@/ui/Overlays'
import { Input, Select } from '@/ui/Field'
import type { CrewRow } from '@/pages/Crew/Index'

interface CrewDocument {
  id: number
  type: string
  expires_on: string | null
  is_expired: boolean
  is_expiring: boolean
}

/** Documents are the point of this screen: expiry is a hard gate on dispatch. */
export default function CrewShow({
  record,
  documents = [],
  can,
}: {
  record: CrewRow & { notes: string | null }
  documents?: CrewDocument[]
  can: { update?: boolean; delete?: boolean }
}) {
  const [open, setOpen] = useState(false)
  const form = useForm({ type: 'visa', number: '', issued_on: '', expires_on: '' })

  return (
    <>
      <DetailShell
        title={record.full_name}
        subtitle={`${record.role} · ${record.reference ?? ''}`}
        status={record.status.replace('_', ' ')}
        statusTone={record.status_tone}
        editUrl={can.update ? `/crew/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/crew/${record.id}` : undefined}
        backUrl="/crew"
        facts={[
          { label: 'Role', value: record.role },
          { label: 'Employment', value: record.employment_type },
          { label: 'Nationality', value: record.nationality ?? '—' },
          { label: 'Mobile', value: <span className="numeric">{record.mobile ?? '—'}</span> },
          {
            label: 'Day rate',
            value: record.day_rate ? <Money amount={record.day_rate} currency={record.currency} /> : 'Restricted',
          },
        ]}
      >
        {record.has_expired_documents && (
          <p className="rounded-card border border-danger bg-danger-bg px-4 py-3 text-small text-danger">
            A document has expired. This person cannot be dispatched until it is renewed.
          </p>
        )}

        <Card>
          <CardHeader>
            <CardTitle>Documents</CardTitle>
            <Button size="sm" variant="secondary" icon={<Plus className="size-4" />} onClick={() => setOpen(true)}>
              Add document
            </Button>
          </CardHeader>
          {documents.length === 0 ? (
            <EmptyState
              icon={<FileText className="size-5" aria-hidden />}
              title="No documents on file"
              description="Visas, seaman books, STCW certificates and medicals all expire — and dispatch reads them."
            />
          ) : (
            <ul className="divide-y divide-line">
              {documents.map((document) => (
                <li key={document.id} className="flex items-center justify-between gap-3 px-5 py-3">
                  <span className="text-h3 text-ink">{document.type.replace(/_/g, ' ')}</span>
                  <span className="flex items-center gap-3">
                    <DateText value={document.expires_on} className="text-small text-ink-soft" />
                    <StatusPill tone={document.is_expired ? 'danger' : document.is_expiring ? 'warning' : 'success'}>
                      {document.is_expired ? 'Expired' : document.is_expiring ? 'Expiring' : 'Valid'}
                    </StatusPill>
                  </span>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </DetailShell>

      <Drawer
        open={open}
        onOpenChange={setOpen}
        title="Add a document"
        footer={
          <>
            <Button variant="secondary" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={form.processing}
              onClick={() =>
                form.post(`/crew/${record.id}/documents`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    form.reset()
                    setOpen(false)
                  },
                })
              }
            >
              Save document
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Type"
            required
            value={form.data.type}
            onChange={(event) => form.setData('type', event.target.value)}
            options={[
              { value: 'visa', label: 'Visa' },
              { value: 'seaman_book', label: "Seaman's book" },
              { value: 'stcw', label: 'STCW' },
              { value: 'medical', label: 'Medical' },
              { value: 'licence', label: 'Licence' },
              { value: 'passport', label: 'Passport' },
            ]}
          />
          <Input label="Number" value={form.data.number} onChange={(event) => form.setData('number', event.target.value)} />
          <Input
            label="Issued on"
            type="date"
            value={form.data.issued_on}
            onChange={(event) => form.setData('issued_on', event.target.value)}
          />
          <Input
            label="Expires on"
            type="date"
            required
            value={form.data.expires_on}
            error={form.errors.expires_on}
            help="Dispatch is blocked once this date passes."
            onChange={(event) => form.setData('expires_on', event.target.value)}
          />
        </div>
      </Drawer>
    </>
  )
}
