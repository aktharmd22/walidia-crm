import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { ClipboardCheck } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Checkbox, Input, Textarea } from '@/ui/Field'
import type { DamageRow } from '@/pages/Charter/DamageReports/Index'

/**
 * Closing this inspection is what releases the client's deposit — the screen
 * says so, because the person closing it is deciding how much money goes back.
 */
export default function DamageReportShow({
  record,
  can,
}: {
  record: DamageRow & {
    description: string
    resolution: string | null
    closed_at: string | null
    deduct_from_deposit: boolean
  }
  can: { update?: boolean; delete?: boolean; close?: boolean }
}) {
  const [closing, setClosing] = useState(false)
  const form = useForm({
    resolution: '',
    actual_cost: record.estimated_cost ?? '',
    deduct_from_deposit: record.deduct_from_deposit,
  })

  return (
    <>
      <DetailShell
        title={record.reference ?? 'Damage report'}
        subtitle={record.booking?.reference ?? null}
        status={record.inspection_status.replace(/_/g, ' ')}
        statusTone={record.status_tone}
        editUrl={can.update ? `/charter/damage-reports/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/charter/damage-reports/${record.id}` : undefined}
        backUrl="/charter/damage-reports"
        actions={[
          can.close && !record.is_closed ? (
            <Button key="close" variant="primary" icon={<ClipboardCheck className="size-4" />} onClick={() => setClosing(true)}>
              Close inspection
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Discovered', value: <DateText value={record.discovered_at} withTime /> },
          { label: 'Yacht', value: record.yacht ?? '—' },
          { label: 'Estimated', value: record.estimated_cost ? <Money amount={record.estimated_cost} /> : '—' },
          { label: 'Actual', value: record.actual_cost ? <Money amount={record.actual_cost} /> : 'Not settled' },
          { label: 'Deducting', value: record.deduct_from_deposit ? 'Yes' : 'No' },
          { label: 'Closed', value: record.closed_at ? <DateText value={record.closed_at} withTime /> : 'Open' },
        ]}
      >
        {!record.is_closed && (
          <p className="rounded-card border border-warning bg-warning-bg px-4 py-3 text-small text-warning">
            The security deposit on this charter stays held until this inspection is closed.
          </p>
        )}

        <Card>
          <CardHeader>
            <CardTitle>What was found</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-wrap text-body text-ink">{record.description}</p>
          </CardBody>
        </Card>

        {record.resolution && (
          <Card>
            <CardHeader>
              <CardTitle>How it was resolved</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.resolution}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={closing}
        onOpenChange={setClosing}
        title="Close the inspection"
        description="This releases the hold on the security deposit. Record what it actually cost."
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
                form.post(`/charter/damage-reports/${record.id}/close`, {
                  preserveScroll: true,
                  onSuccess: () => setClosing(false),
                })
              }
            >
              Close inspection
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Textarea
            label="Outcome"
            required
            rows={4}
            value={form.data.resolution}
            error={form.errors.resolution}
            help="At least 20 characters. Quoted to the client if anything is deducted."
            onChange={(event) => form.setData('resolution', event.target.value)}
          />
          <Input
            label="Actual cost"
            type="number"
            step="0.01"
            value={String(form.data.actual_cost ?? '')}
            onChange={(event) => form.setData('actual_cost', event.target.value)}
          />
          <Checkbox
            label="Deduct from the security deposit"
            checked={form.data.deduct_from_deposit}
            onChange={(event) => form.setData('deduct_from_deposit', event.target.checked)}
          />
        </div>
      </Modal>
    </>
  )
}
