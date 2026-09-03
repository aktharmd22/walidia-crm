import { useState } from 'react'
import { Link, useForm } from '@inertiajs/react'
import { Undo2 } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { GateCleared, GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Modal } from '@/ui/Overlays'
import { Input, Textarea } from '@/ui/Field'
import type { GateResult } from '@/types'
import type { DepositRow } from '@/pages/Finance/SecurityDeposits/Index'

interface OpenDamage {
  id: number
  reference: string | null
  description: string
  estimated_cost: string | null
}

/**
 * Releasing a deposit is the last money decision on a charter, and the easiest
 * one to get wrong under pressure from a departing client. The gate is shown in
 * full, and an override is a written act.
 */
export default function SecurityDepositShow({
  record,
  gate,
  openDamage = [],
  can,
}: {
  record: DepositRow & { released_amount: string | null; deduction_reason: string | null }
  gate: GateResult
  openDamage?: OpenDamage[]
  can: { release?: boolean; update?: boolean; override?: boolean }
}) {
  const [releasing, setReleasing] = useState(false)
  const form = useForm({ released_amount: record.amount, deduction_reason: '', override_reason: '' })
  const blocked = gate.verdict === 'block'

  return (
    <>
      <DetailShell
        title={`${record.currency} ${record.amount}`}
        subtitle={record.booking?.reference ?? null}
        status={record.status.replace(/_/g, ' ')}
        statusTone={record.status_tone}
        editUrl={can.update && record.is_held ? `/finance/security-deposits/${record.id}/edit` : undefined}
        backUrl="/finance/security-deposits"
        actions={[
          can.release && record.is_held ? (
            <Button
              key="release"
              variant={blocked ? 'secondary' : 'primary'}
              icon={<Undo2 className="size-4" />}
              disabled={blocked && !can.override}
              onClick={() => setReleasing(true)}
            >
              {blocked ? 'Release (blocked)' : 'Release deposit'}
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Client', value: record.booking?.client ?? '—' },
          { label: 'Method', value: record.method.replace(/_/g, ' ') },
          { label: 'Collected', value: <DateText value={record.collected_at} withTime /> },
          {
            label: 'Released',
            value: record.released_amount ? <Money amount={record.released_amount} currency={record.currency} /> : 'Still held',
          },
          { label: 'Released at', value: record.released_at ? <DateText value={record.released_at} withTime /> : '—' },
          { label: 'Deduction', value: record.deduction_reason ?? 'None' },
        ]}
      >
        {record.is_held && (blocked ? <GatePanel gate={gate} /> : <GateCleared label="Ready to release" />)}

        {openDamage.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>Why it is held</CardTitle>
            </CardHeader>
            <ul className="divide-y divide-line">
              {openDamage.map((damage) => (
                <li key={damage.id} className="flex flex-wrap items-center gap-3 px-5 py-3">
                  <Link href={`/charter/damage-reports/${damage.id}`} className="min-w-0 flex-1 text-h3 text-ink hover:text-accent-ink">
                    {damage.reference ?? 'Damage report'}
                    <span className="block text-small text-ink-faint">{damage.description}</span>
                  </Link>
                  {damage.estimated_cost && <Money amount={damage.estimated_cost} currency={record.currency} />}
                  <StatusPill tone="warning">Inspection open</StatusPill>
                </li>
              ))}
            </ul>
          </Card>
        )}

        {record.deduction_reason && (
          <Card>
            <CardHeader>
              <CardTitle>What was deducted, and why</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="text-body text-ink">{record.deduction_reason}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={releasing}
        onOpenChange={setReleasing}
        title="Release the security deposit"
        description="Release the full amount, or less with a reason the client can be shown."
        footer={
          <>
            <Button variant="secondary" onClick={() => setReleasing(false)}>
              Cancel
            </Button>
            <Button
              variant={blocked ? 'destructive' : 'primary'}
              loading={form.processing}
              disabled={blocked && form.data.override_reason.trim().length < 20}
              onClick={() =>
                form.post(`/finance/security-deposits/${record.id}/release`, {
                  preserveScroll: true,
                  onSuccess: () => setReleasing(false),
                })
              }
            >
              {blocked ? 'Override and release' : 'Release deposit'}
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input
            label="Amount to release"
            type="number"
            step="0.01"
            required
            value={form.data.released_amount}
            error={form.errors.released_amount}
            help={`Held: ${record.amount} ${record.currency}`}
            onChange={(event) => form.setData('released_amount', event.target.value)}
          />
          <Input
            label="Reason for any deduction"
            value={form.data.deduction_reason}
            onChange={(event) => form.setData('deduction_reason', event.target.value)}
          />
          {blocked && (
            <Textarea
              label="Override reason"
              required
              rows={3}
              value={form.data.override_reason}
              error={form.errors.override_reason}
              help="At least 20 characters. Recorded in the Override Register against your name."
              onChange={(event) => form.setData('override_reason', event.target.value)}
            />
          )}
        </div>
      </Modal>
    </>
  )
}
