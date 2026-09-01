import { useState } from 'react'
import { router, useForm } from '@inertiajs/react'
import { PhoneCall, UserPlus } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Modal } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import { DateText } from '@/ui/Primitives'
import type { StatusTone } from '@/types'

interface LeadRecord {
  id: number
  reference: string
  name: string
  email: string | null
  mobile: string | null
  message: string | null
  business_line: string
  status: string
  status_tone: StatusTone
  source?: string | null
  assignee?: { id: number; name: string } | null
  client?: { id: number; name: string } | null
  sla_due_at: string | null
  first_response_at: string | null
  is_overdue: boolean
  converted_at: string | null
  created_at: string | null
}

export default function LeadShow({
  record,
  timeline = [],
  can,
}: {
  record: LeadRecord
  timeline?: TimelineEntry[]
  can: { update?: boolean; delete?: boolean; convert?: boolean }
}) {
  const [contactOpen, setContactOpen] = useState(false)
  const contact = useForm({ channel: 'whatsapp', summary: '', body: '', next_follow_up_at: '' })

  return (
    <>
      <DetailShell
        title={record.name}
        subtitle={`${record.reference} · ${record.business_line}`}
        status={record.status}
        statusTone={record.status_tone}
        editUrl={can.update ? `/leads/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/leads/${record.id}` : undefined}
        backUrl="/leads"
        actions={[
          <Button
            key="contact"
            variant="secondary"
            icon={<PhoneCall className="size-4" />}
            onClick={() => setContactOpen(true)}
          >
            Log contact
          </Button>,
          can.convert && record.converted_at === null ? (
            <Button
              key="convert"
              variant="primary"
              icon={<UserPlus className="size-4" />}
              onClick={() => router.post(`/leads/${record.id}/convert`, { create_deal: true })}
            >
              Convert to client
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Reference', value: <span className="numeric">{record.reference}</span> },
          { label: 'Mobile', value: <span className="numeric">{record.mobile ?? '—'}</span> },
          { label: 'Email', value: record.email ?? '—' },
          { label: 'Source', value: record.source ?? '—' },
          { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
          { label: 'Respond by', value: <DateText value={record.sla_due_at} withTime /> },
          { label: 'First response', value: <DateText value={record.first_response_at} withTime /> },
          { label: 'Received', value: <DateText value={record.created_at} withTime /> },
        ]}
        timeline={timeline}
      >
        {record.message && (
          <div className="rounded-card border border-line bg-hull p-5">
            <p className="text-micro text-ink-faint">Original enquiry</p>
            <p className="mt-2 whitespace-pre-line text-body text-ink">{record.message}</p>
          </div>
        )}
      </DetailShell>

      <Modal
        open={contactOpen}
        onOpenChange={setContactOpen}
        title="Log contact"
        description="Recording the first response stops the SLA clock and takes this lead out of the follow-up pool."
        footer={
          <>
            <Button variant="secondary" onClick={() => setContactOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={contact.processing}
              onClick={() =>
                contact.post(`/leads/${record.id}/log-contact`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    contact.reset()
                    setContactOpen(false)
                  },
                })
              }
            >
              Log it
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Channel"
            value={contact.data.channel}
            error={contact.errors.channel}
            onChange={(event) => contact.setData('channel', event.target.value)}
            options={[
              { value: 'whatsapp', label: 'WhatsApp' },
              { value: 'call', label: 'Call' },
              { value: 'email', label: 'Email' },
              { value: 'meeting', label: 'Meeting' },
            ]}
          />
          <Input
            label="Summary"
            required
            value={contact.data.summary}
            error={contact.errors.summary}
            onChange={(event) => contact.setData('summary', event.target.value)}
          />
          <Textarea
            label="Detail"
            value={contact.data.body}
            error={contact.errors.body}
            onChange={(event) => contact.setData('body', event.target.value)}
          />
          <Input
            label="Next follow-up"
            type="datetime-local"
            value={contact.data.next_follow_up_at}
            error={contact.errors.next_follow_up_at}
            onChange={(event) => contact.setData('next_follow_up_at', event.target.value)}
          />
        </div>
      </Modal>
    </>
  )
}
