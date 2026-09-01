import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { Send, Reply } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { GateCleared, GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Select, Textarea } from '@/ui/Field'
import type { GateResult } from '@/types'
import type { OfferRow } from '@/pages/Brokerage/Offers/Index'

/**
 * A seller taking their yacht off the market is entitled to know the buyer can
 * complete — so submission is gated on proof of funds, and the screen says so.
 */
export default function OfferShow({
  record,
  gate,
  can,
}: {
  record: OfferRow
  gate: GateResult
  can: { update?: boolean; delete?: boolean; submit?: boolean; respond?: boolean; override?: boolean }
}) {
  const [submitting, setSubmitting] = useState(false)
  const [responding, setResponding] = useState(false)
  const submit = useForm({ override_reason: '' })
  const respond = useForm({ status: 'accepted', response_notes: '' })
  const blocked = gate.verdict === 'block'

  return (
    <>
      <DetailShell
        title={`${record.currency} ${record.amount}`}
        subtitle={`${record.client ?? ''} · ${record.listing ?? ''}`}
        status={record.status}
        statusTone={record.status_tone}
        editUrl={can.update && record.status === 'draft' ? `/brokerage/offers/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/brokerage/offers/${record.id}` : undefined}
        backUrl="/brokerage/offers"
        actions={[
          can.submit && record.status === 'draft' ? (
            <Button
              key="submit"
              variant={blocked ? 'secondary' : 'primary'}
              icon={<Send className="size-4" />}
              disabled={blocked && !can.override}
              onClick={() => setSubmitting(true)}
            >
              {blocked ? 'Submit (blocked)' : 'Submit to seller'}
            </Button>
          ) : null,
          can.respond ? (
            <Button key="respond" variant="secondary" icon={<Reply className="size-4" />} onClick={() => setResponding(true)}>
              Record response
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Offer', value: <Money amount={record.amount} currency={record.currency} /> },
          { label: 'Deposit', value: record.deposit_amount ? <Money amount={record.deposit_amount} currency={record.currency} /> : '—' },
          { label: 'Valid until', value: <DateText value={record.valid_until} /> },
          { label: 'Proof of funds', value: record.proof_of_funds_received ? 'Received' : 'Not received' },
          { label: 'Subject to survey', value: record.subject_to_survey ? 'Yes' : 'No' },
          { label: 'Submitted', value: <DateText value={record.submitted_at} withTime /> },
        ]}
      >
        {record.status === 'draft' &&
          (blocked ? <GatePanel gate={gate} /> : <GateCleared label="Proof of funds on file — ready to submit" />)}

        {record.conditions && (
          <Card>
            <CardHeader>
              <CardTitle>Conditions</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.conditions}</p>
            </CardBody>
          </Card>
        )}

        {record.response_notes && (
          <Card>
            <CardHeader>
              <CardTitle>The seller's response</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.response_notes}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={submitting}
        onOpenChange={setSubmitting}
        title="Submit the offer"
        description="The listing moves to under offer once this is sent."
        footer={
          <>
            <Button variant="secondary" onClick={() => setSubmitting(false)}>
              Cancel
            </Button>
            <Button
              variant={blocked ? 'destructive' : 'primary'}
              loading={submit.processing}
              disabled={blocked && submit.data.override_reason.trim().length < 20}
              onClick={() =>
                submit.post(`/brokerage/offers/${record.id}/submit`, {
                  preserveScroll: true,
                  onSuccess: () => setSubmitting(false),
                })
              }
            >
              {blocked ? 'Override and submit' : 'Submit'}
            </Button>
          </>
        }
      >
        {blocked ? (
          <Textarea
            label="Override reason"
            required
            rows={3}
            value={submit.data.override_reason}
            error={submit.errors.override_reason}
            help="At least 20 characters. Recorded in the Override Register against your name."
            onChange={(event) => submit.setData('override_reason', event.target.value)}
          />
        ) : (
          <p className="text-body text-ink-soft">Proof of funds is on file. The offer goes to the seller as it stands.</p>
        )}
      </Modal>

      <Modal
        open={responding}
        onOpenChange={setResponding}
        title="Record the seller's response"
        footer={
          <>
            <Button variant="secondary" onClick={() => setResponding(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={respond.processing}
              onClick={() =>
                respond.post(`/brokerage/offers/${record.id}/respond`, {
                  preserveScroll: true,
                  onSuccess: () => setResponding(false),
                })
              }
            >
              Save response
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Response"
            required
            value={respond.data.status}
            onChange={(event) => respond.setData('status', event.target.value)}
            options={[
              { value: 'accepted', label: 'Accepted' },
              { value: 'countered', label: 'Countered' },
              { value: 'rejected', label: 'Rejected' },
            ]}
          />
          <Textarea
            label="In the seller's words"
            required
            rows={4}
            value={respond.data.response_notes}
            error={respond.errors.response_notes}
            onChange={(event) => respond.setData('response_notes', event.target.value)}
          />
        </div>
      </Modal>
    </>
  )
}
