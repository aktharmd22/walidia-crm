import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { CheckCircle2 } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Modal } from '@/ui/Overlays'
import { Textarea } from '@/ui/Field'
import type { IncidentRow } from '@/pages/Charter/Incidents/Index'

/**
 * An incident closes with a written outcome or it does not close. There is no
 * button here that quietly marks it resolved.
 */
export default function IncidentShow({
  record,
  can,
}: {
  record: IncidentRow & {
    description: string
    immediate_action: string | null
    injuries: boolean
    authorities_notified: boolean
    closed_at: string | null
  }
  can: { update?: boolean; delete?: boolean; close?: boolean }
}) {
  const [closing, setClosing] = useState(false)
  const form = useForm({ resolution: '' })

  return (
    <>
      <DetailShell
        title={record.reference ?? 'Incident'}
        subtitle={record.type.replace(/_/g, ' ')}
        status={record.status}
        statusTone={record.status_tone}
        editUrl={can.update ? `/charter/incidents/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/charter/incidents/${record.id}` : undefined}
        backUrl="/charter/incidents"
        actions={[
          can.close && record.status !== 'closed' ? (
            <Button key="close" variant="primary" icon={<CheckCircle2 className="size-4" />} onClick={() => setClosing(true)}>
              Close incident
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Severity', value: <StatusPill tone={record.severity_tone}>{record.severity}</StatusPill> },
          { label: 'Occurred', value: <DateText value={record.occurred_at} withTime /> },
          { label: 'Charter', value: record.booking?.reference ?? '—' },
          { label: 'Yacht', value: record.yacht ?? '—' },
          { label: 'Injuries', value: record.injuries ? 'Yes' : 'No' },
          { label: 'Authorities notified', value: record.authorities_notified ? 'Yes' : 'No' },
          { label: 'Closed', value: record.closed_at ? <DateText value={record.closed_at} withTime /> : 'Open' },
        ]}
      >
        <Card>
          <CardHeader>
            <CardTitle>What happened</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-wrap text-body text-ink">{record.description}</p>
          </CardBody>
        </Card>

        {record.immediate_action && (
          <Card>
            <CardHeader>
              <CardTitle>What was done at the time</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.immediate_action}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={closing}
        onOpenChange={setClosing}
        title="Close this incident"
        description="The outcome is written into the charter timeline and cannot be edited away."
        footer={
          <>
            <Button variant="secondary" onClick={() => setClosing(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={form.processing}
              disabled={form.data.resolution.trim().length < 20}
              onClick={() =>
                form.post(`/charter/incidents/${record.id}/close`, {
                  preserveScroll: true,
                  onSuccess: () => setClosing(false),
                })
              }
            >
              Close incident
            </Button>
          </>
        }
      >
        <Textarea
          label="Outcome"
          required
          rows={5}
          value={form.data.resolution}
          error={form.errors.resolution}
          help="At least 20 characters. This is the record an insurer will read."
          onChange={(event) => form.setData('resolution', event.target.value)}
        />
      </Modal>
    </>
  )
}
