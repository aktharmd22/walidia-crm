import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { AlertCircle, MessageSquare, Sparkles } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Modal } from '@/ui/Overlays'
import { Checkbox, Select, Textarea } from '@/ui/Field'
import type { JourneyRow } from '@/pages/Crm/Journeys/Index'

const UPSELLS = [
  { value: 'brokerage', label: 'Yacht brokerage' },
  { value: 'yacht_sales', label: 'Yacht sales' },
  { value: 'management', label: 'Yacht management' },
  { value: 'crew_services', label: 'Crew services' },
  { value: 'maintenance', label: 'Maintenance' },
  { value: 'annual_package', label: 'Annual charter package' },
  { value: 'membership', label: 'Membership' },
  { value: 'insurance', label: 'Insurance' },
]

/**
 * One client's after-care, as a sequence rather than a checklist: what has
 * gone out, what came back, and what they might want next.
 */
export default function JourneyShow({
  record,
  can,
}: {
  record: JourneyRow
  can: { update?: boolean; delete?: boolean }
}) {
  const [surveying, setSurveying] = useState(false)
  const [complaining, setComplaining] = useState(false)
  const [resolving, setResolving] = useState(false)
  const [upselling, setUpselling] = useState(false)

  const survey = useForm({ satisfaction_score: '5', survey_response: '' })
  const complaint = useForm({ complaint_detail: '' })
  const resolution = useForm({ complaint_resolution: '' })
  const upsell = useForm<{ upsell_interests: string[] }>({ upsell_interests: record.upsell_interests ?? [] })

  const steps = [
    { label: 'Thank you', at: record.thank_you_sent_at },
    { label: 'Feedback requested', at: record.feedback_requested_at },
    { label: 'Review requested', at: record.review_requested_at },
    { label: 'Survey sent', at: record.survey_sent_at },
  ]

  return (
    <>
      <DetailShell
        title={record.client ?? 'Client journey'}
        subtitle={record.type.replace(/_/g, ' ')}
        status={record.status}
        statusTone={record.status_tone}
        editUrl={can.update ? `/crm/journeys/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/crm/journeys/${record.id}` : undefined}
        backUrl="/crm/journeys"
        actions={[
          can.update ? (
            <Button key="survey" variant="secondary" icon={<MessageSquare className="size-4" />} onClick={() => setSurveying(true)}>
              Record response
            </Button>
          ) : null,
          can.update && !record.complaint_raised ? (
            <Button key="complaint" variant="secondary" icon={<AlertCircle className="size-4" />} onClick={() => setComplaining(true)}>
              Raise complaint
            </Button>
          ) : null,
          can.update && record.has_open_complaint ? (
            <Button key="resolve" variant="primary" onClick={() => setResolving(true)}>
              Resolve complaint
            </Button>
          ) : null,
          can.update ? (
            <Button key="upsell" variant="secondary" icon={<Sparkles className="size-4" />} onClick={() => setUpselling(true)}>
              Interests
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Satisfaction', value: record.satisfaction_score ? `${record.satisfaction_score} / 5` : 'Not asked' },
          { label: 'Thank you', value: <DateText value={record.thank_you_sent_at} /> },
          { label: 'Feedback', value: <DateText value={record.feedback_requested_at} /> },
          { label: 'Review', value: <DateText value={record.review_requested_at} /> },
        ]}
      >
        {record.has_open_complaint && (
          <p className="rounded-card border border-danger bg-danger-bg px-4 py-3 text-small text-danger">
            An unresolved complaint is open on this client. {record.complaint_detail}
          </p>
        )}

        <Card>
          <CardHeader>
            <CardTitle>What has gone out</CardTitle>
          </CardHeader>
          <ul className="divide-y divide-line">
            {steps.map((step) => (
              <li key={step.label} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="text-body text-ink">{step.label}</span>
                {step.at ? (
                  <DateText value={step.at} className="text-small text-ink-soft" />
                ) : (
                  <StatusPill tone="neutral">Pending</StatusPill>
                )}
              </li>
            ))}
            {[7, 30, 90, 180, 365].map((days) => (
              <li key={days} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="text-body text-ink-soft">{days}-day follow-up</span>
                {record.follow_ups_sent?.[String(days)] ? (
                  <DateText value={record.follow_ups_sent[String(days)]} className="text-small text-ink-soft" />
                ) : (
                  <StatusPill tone="neutral">Not due</StatusPill>
                )}
              </li>
            ))}
          </ul>
        </Card>

        {record.survey_response && (
          <Card>
            <CardHeader>
              <CardTitle>What the client said</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.survey_response}</p>
            </CardBody>
          </Card>
        )}

        {record.complaint_resolution && (
          <Card>
            <CardHeader>
              <CardTitle>How the complaint was resolved</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.complaint_resolution}</p>
            </CardBody>
          </Card>
        )}

        {(record.upsell_interests ?? []).length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>What they might want next</CardTitle>
            </CardHeader>
            <CardBody>
              <div className="flex flex-wrap gap-2">
                {record.upsell_interests?.map((interest) => (
                  <StatusPill key={interest} tone="info">
                    {UPSELLS.find((option) => option.value === interest)?.label ?? interest}
                  </StatusPill>
                ))}
              </div>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={surveying}
        onOpenChange={setSurveying}
        title="Record the client's response"
        footer={
          <>
            <Button variant="secondary" onClick={() => setSurveying(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={survey.processing}
              onClick={() =>
                survey.post(`/crm/journeys/${record.id}/survey`, {
                  preserveScroll: true,
                  onSuccess: () => setSurveying(false),
                })
              }
            >
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Satisfaction"
            required
            value={survey.data.satisfaction_score}
            onChange={(event) => survey.setData('satisfaction_score', event.target.value)}
            options={[5, 4, 3, 2, 1].map((score) => ({ value: String(score), label: `${score} / 5` }))}
          />
          <Textarea
            label="In their words"
            rows={4}
            value={survey.data.survey_response}
            onChange={(event) => survey.setData('survey_response', event.target.value)}
          />
        </div>
      </Modal>

      <Modal
        open={complaining}
        onOpenChange={setComplaining}
        title="Raise a complaint"
        description="Recorded now, resolved separately — the gap between the two is what is worth measuring."
        footer={
          <>
            <Button variant="secondary" onClick={() => setComplaining(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              loading={complaint.processing}
              onClick={() =>
                complaint.post(`/crm/journeys/${record.id}/complaint`, {
                  preserveScroll: true,
                  onSuccess: () => setComplaining(false),
                })
              }
            >
              Record complaint
            </Button>
          </>
        }
      >
        <Textarea
          label="What went wrong"
          required
          rows={4}
          value={complaint.data.complaint_detail}
          error={complaint.errors.complaint_detail}
          onChange={(event) => complaint.setData('complaint_detail', event.target.value)}
        />
      </Modal>

      <Modal
        open={resolving}
        onOpenChange={setResolving}
        title="Resolve the complaint"
        footer={
          <>
            <Button variant="secondary" onClick={() => setResolving(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={resolution.processing}
              disabled={resolution.data.complaint_resolution.trim().length < 20}
              onClick={() =>
                resolution.post(`/crm/journeys/${record.id}/complaint/resolve`, {
                  preserveScroll: true,
                  onSuccess: () => setResolving(false),
                })
              }
            >
              Resolve
            </Button>
          </>
        }
      >
        <Textarea
          label="What was done about it"
          required
          rows={4}
          value={resolution.data.complaint_resolution}
          error={resolution.errors.complaint_resolution}
          help="At least 20 characters. This is what the client will be told."
          onChange={(event) => resolution.setData('complaint_resolution', event.target.value)}
        />
      </Modal>

      <Modal
        open={upselling}
        onOpenChange={setUpselling}
        title="What might they want next?"
        description="Recorded from what the client actually said, not guessed."
        footer={
          <>
            <Button variant="secondary" onClick={() => setUpselling(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={upsell.processing}
              onClick={() =>
                upsell.post(`/crm/journeys/${record.id}/upsell`, {
                  preserveScroll: true,
                  onSuccess: () => setUpselling(false),
                })
              }
            >
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-2">
          {UPSELLS.map((option) => (
            <Checkbox
              key={option.value}
              label={option.label}
              checked={upsell.data.upsell_interests.includes(option.value)}
              onChange={(event) =>
                upsell.setData(
                  'upsell_interests',
                  event.target.checked
                    ? [...upsell.data.upsell_interests, option.value]
                    : upsell.data.upsell_interests.filter((value) => value !== option.value),
                )
              }
            />
          ))}
        </div>
      </Modal>
    </>
  )
}
