import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { CalendarCheck, MessageSquare } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { GateCleared, GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import type { GateResult } from '@/types'
import type { ViewingRow } from '@/pages/Brokerage/Viewings/Index'

/**
 * Scheduling is the guarded step. The gate names what is missing — the NDA or
 * the buyer's KYC — and links to the screen that fixes it.
 */
export default function ViewingShow({
  record,
  gate,
  can,
}: {
  record: ViewingRow
  gate: GateResult
  can: { update?: boolean; delete?: boolean; schedule?: boolean; override?: boolean }
}) {
  const [scheduling, setScheduling] = useState(false)
  const [completing, setCompleting] = useState(false)
  const schedule = useForm({ scheduled_at: '', marina_id: '', override_reason: '' })
  const complete = useForm({ feedback: '', interest_level: '3' })
  const blocked = gate.verdict === 'block'
  const isScheduled = record.status !== 'requested'

  return (
    <>
      <DetailShell
        title={record.client ?? 'Viewing'}
        subtitle={record.listing}
        status={record.status.replace(/_/g, ' ')}
        statusTone={record.status_tone}
        editUrl={can.update ? `/brokerage/viewings/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/brokerage/viewings/${record.id}` : undefined}
        backUrl="/brokerage/viewings"
        actions={[
          can.schedule && !isScheduled ? (
            <Button
              key="schedule"
              variant={blocked ? 'secondary' : 'primary'}
              icon={<CalendarCheck className="size-4" />}
              disabled={blocked && !can.override}
              onClick={() => setScheduling(true)}
            >
              {blocked ? 'Schedule (blocked)' : 'Schedule viewing'}
            </Button>
          ) : null,
          can.update && record.status === 'scheduled' ? (
            <Button key="complete" variant="secondary" icon={<MessageSquare className="size-4" />} onClick={() => setCompleting(true)}>
              Record feedback
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'When', value: <DateText value={record.scheduled_at} withTime /> },
          { label: 'Duration', value: <span className="numeric">{record.duration_minutes} min</span> },
          { label: 'Attending', value: record.attendees ?? '—' },
          { label: 'Interest', value: record.interest_level ? <span className="numeric">{record.interest_level} / 5</span> : '—' },
          { label: 'Completed', value: <DateText value={record.completed_at} withTime /> },
        ]}
      >
        {!isScheduled && (blocked ? <GatePanel gate={gate} /> : <GateCleared label="NDA signed and buyer verified — ready to schedule" />)}

        {record.feedback && (
          <Card>
            <CardHeader>
              <CardTitle>What the buyer said</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.feedback}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={scheduling}
        onOpenChange={setScheduling}
        title="Schedule the viewing"
        description="Confirm the time the buyer will come aboard."
        footer={
          <>
            <Button variant="secondary" onClick={() => setScheduling(false)}>
              Cancel
            </Button>
            <Button
              variant={blocked ? 'destructive' : 'primary'}
              loading={schedule.processing}
              disabled={blocked && schedule.data.override_reason.trim().length < 20}
              onClick={() =>
                schedule.post(`/brokerage/viewings/${record.id}/schedule`, {
                  preserveScroll: true,
                  onSuccess: () => setScheduling(false),
                })
              }
            >
              {blocked ? 'Override and schedule' : 'Schedule'}
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input
            label="Date and time"
            type="datetime-local"
            required
            value={schedule.data.scheduled_at}
            error={schedule.errors.scheduled_at}
            onChange={(event) => schedule.setData('scheduled_at', event.target.value)}
          />
          {blocked && (
            <Textarea
              label="Override reason"
              required
              rows={3}
              value={schedule.data.override_reason}
              error={schedule.errors.override_reason}
              help="At least 20 characters. Recorded in the Override Register against your name."
              onChange={(event) => schedule.setData('override_reason', event.target.value)}
            />
          )}
        </div>
      </Modal>

      <Modal
        open={completing}
        onOpenChange={setCompleting}
        title="Record the viewing"
        description="What the buyer said, while it is still fresh."
        footer={
          <>
            <Button variant="secondary" onClick={() => setCompleting(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={complete.processing}
              onClick={() =>
                complete.post(`/brokerage/viewings/${record.id}/complete`, {
                  preserveScroll: true,
                  onSuccess: () => setCompleting(false),
                })
              }
            >
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Textarea
            label="Feedback"
            required
            rows={5}
            value={complete.data.feedback}
            error={complete.errors.feedback}
            onChange={(event) => complete.setData('feedback', event.target.value)}
          />
          <Select
            label="Interest"
            value={complete.data.interest_level}
            onChange={(event) => complete.setData('interest_level', event.target.value)}
            options={[5, 4, 3, 2, 1].map((score) => ({ value: String(score), label: `${score} / 5` }))}
          />
        </div>
      </Modal>
    </>
  )
}
