import { useState } from 'react'
import { Link, useForm } from '@inertiajs/react'
import { EyeOff, FileText, ShieldCheck } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Modal } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import type { StatusTone } from '@/types'

interface ClientRecord {
  id: number
  reference: string
  full_name: string
  full_name_ar: string | null
  client_type: string[]
  email: string | null
  mobile: string | null
  phone_alt: string | null
  preferred_channel: string
  nationality: string | null
  city: string | null
  country: string | null
  vip_level: string
  status: string
  status_tone: StatusTone
  kyc_status: string
  kyc_tone: StatusTone
  kyc_verified_at: string | null
  kyc_expires_on: string | null
  company?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  notes: string | null
  created_at: string | null
  // VIP group — absent from the payload entirely without the permission.
  passport_number?: string
  passport_expiry?: string | null
  emirates_id?: string
  date_of_birth?: string | null
  dietary_preferences?: string
  allergies?: string
  notes_vip?: string
  vip_fields_hidden: boolean
}

export default function ClientShow({
  record,
  timeline = [],
  tasks = [],
  documents = [],
  can,
  canViewVip,
}: {
  record: ClientRecord
  timeline?: TimelineEntry[]
  tasks?: { id: number; title: string; due_at: string | null; overdue: boolean }[]
  documents?: { id: number; title: string; category: string; expires_on: string | null }[]
  can: { update?: boolean; delete?: boolean }
  canViewVip: boolean
}) {
  const [kycOpen, setKycOpen] = useState(false)
  const kyc = useForm({ outcome: 'verified', expires_on: '', note: '' })

  return (
    <>
      <DetailShell
        title={record.full_name}
        subtitle={`${record.reference}${record.company ? ` · ${record.company.name}` : ''}`}
        status={record.status.replace('_', ' ')}
        statusTone={record.status_tone}
        editUrl={can.update ? `/clients/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/clients/${record.id}` : undefined}
        backUrl="/clients"
        actions={[
          <Button key="kyc" variant="secondary" icon={<ShieldCheck className="size-4" />} onClick={() => setKycOpen(true)}>
            KYC
          </Button>,
        ]}
        facts={[
          { label: 'Reference', value: <span className="numeric">{record.reference}</span> },
          { label: 'Mobile', value: <span className="numeric">{record.mobile ?? '—'}</span> },
          { label: 'Email', value: record.email ?? '—' },
          { label: 'Preferred channel', value: record.preferred_channel },
          { label: 'Nationality', value: record.nationality ?? '—' },
          { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
          { label: 'KYC', value: <StatusPill tone={record.kyc_tone}>{record.kyc_status.replace('_', ' ')}</StatusPill> },
          { label: 'Added', value: <DateText value={record.created_at} /> },
        ]}
        timeline={timeline}
        aside={
          <>
            <Card>
              <CardHeader>
                <CardTitle>Open tasks</CardTitle>
                <Link href="/tasks" className="text-small text-accent-ink hover:underline">
                  All
                </Link>
              </CardHeader>
              {tasks.length === 0 ? (
                <EmptyState title="No open tasks" description="Next actions for this client will appear here." />
              ) : (
                <ul className="divide-y divide-line">
                  {tasks.map((task) => (
                    <li key={task.id} className="flex items-center justify-between gap-3 px-5 py-3">
                      <span className="min-w-0 truncate text-body text-ink">{task.title}</span>
                      <StatusPill tone={task.overdue ? 'danger' : 'neutral'}>
                        <DateText value={task.due_at} />
                      </StatusPill>
                    </li>
                  ))}
                </ul>
              )}
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Documents</CardTitle>
                <Link href={`/documents?subject_type=client&subject_id=${record.id}`} className="text-small text-accent-ink hover:underline">
                  All
                </Link>
              </CardHeader>
              {documents.length === 0 ? (
                <EmptyState
                  icon={<FileText className="size-5" aria-hidden />}
                  title="No documents"
                  description="KYC, contracts and identity documents live in the vault, never on a shared drive."
                />
              ) : (
                <ul className="divide-y divide-line">
                  {documents.map((document) => (
                    <li key={document.id} className="flex items-center justify-between gap-3 px-5 py-3">
                      <span className="min-w-0 truncate text-body text-ink">{document.title}</span>
                      <span className="text-small text-ink-faint">{document.category}</span>
                    </li>
                  ))}
                </ul>
              )}
            </Card>
          </>
        }
      >
        <Card>
          <CardHeader>
            <CardTitle>Identity &amp; preferences</CardTitle>
            {!canViewVip && record.vip_fields_hidden && (
              <StatusPill tone="neutral">
                <EyeOff className="size-3" aria-hidden /> Restricted
              </StatusPill>
            )}
          </CardHeader>
          <CardBody>
            {canViewVip ? (
              <dl className="grid gap-3 md:grid-cols-2">
                <Fact label="Passport" value={record.passport_number} numeric />
                <Fact label="Passport expiry" value={record.passport_expiry} />
                <Fact label="Emirates ID" value={record.emirates_id} numeric />
                <Fact label="Date of birth" value={record.date_of_birth} />
                <Fact label="Dietary preferences" value={record.dietary_preferences} wide />
                <Fact label="Allergies" value={record.allergies} wide />
                <Fact label="VIP notes" value={record.notes_vip} wide />
              </dl>
            ) : (
              <p className="text-body text-ink-soft">
                Identity, dietary and allergy details are restricted on this record. Ask an Admin for VIP access if you
                need them to deliver a charter — the grant and every read are logged.
              </p>
            )}
          </CardBody>
        </Card>

        {record.notes && (
          <Card>
            <CardHeader>
              <CardTitle>Notes</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-line text-body text-ink-soft">{record.notes}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={kycOpen}
        onOpenChange={setKycOpen}
        title="Record a KYC decision"
        description="Contract generation is blocked until KYC is verified — this is the field that gate reads."
        footer={
          <>
            <Button variant="secondary" onClick={() => setKycOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={kyc.processing}
              onClick={() =>
                kyc.post(`/clients/${record.id}/kyc`, {
                  preserveScroll: true,
                  onSuccess: () => setKycOpen(false),
                })
              }
            >
              Save decision
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Outcome"
            value={kyc.data.outcome}
            error={kyc.errors.outcome}
            onChange={(event) => kyc.setData('outcome', event.target.value)}
            options={[
              { value: 'verified', label: 'Verified' },
              { value: 'rejected', label: 'Rejected' },
            ]}
          />
          <Input
            label="Valid until"
            type="date"
            value={kyc.data.expires_on}
            error={kyc.errors.expires_on}
            help="Leave empty if the verification does not expire."
            onChange={(event) => kyc.setData('expires_on', event.target.value)}
          />
          <Textarea
            label="Note"
            value={kyc.data.note}
            error={kyc.errors.note}
            onChange={(event) => kyc.setData('note', event.target.value)}
          />
        </div>
      </Modal>
    </>
  )
}

function Fact({
  label,
  value,
  numeric,
  wide,
}: {
  label: string
  value?: string | null
  numeric?: boolean
  wide?: boolean
}) {
  return (
    <div className={wide ? 'md:col-span-2' : undefined}>
      <dt className="text-small text-ink-faint">{label}</dt>
      <dd className={numeric ? 'numeric text-body text-ink' : 'text-body text-ink'}>{value || '—'}</dd>
    </div>
  )
}
