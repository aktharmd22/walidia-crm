import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { BadgeCheck, Banknote, KeyRound } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { GateCleared, GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import type { GateResult } from '@/types'
import type { TransactionRow } from '@/pages/Brokerage/Transactions/Index'

/**
 * The transfer is the one irreversible act in the whole system. Everything on
 * this screen exists so that it cannot happen a day early.
 */
export default function TransactionShow({
  record,
  gate,
  can,
}: {
  record: TransactionRow
  gate: GateResult
  can: { update?: boolean; delete?: boolean; transfer?: boolean; clearAml?: boolean; override?: boolean }
}) {
  const [transferring, setTransferring] = useState(false)
  const [recordingFunds, setRecordingFunds] = useState(false)
  const [clearingAml, setClearingAml] = useState(false)

  const transfer = useForm({ override_reason: '' })
  const funds = useForm({ leg: 'balance', cleared_at: new Date().toISOString().slice(0, 16) })
  const aml = useForm({ notes: '' })

  const blocked = gate.verdict === 'block'

  return (
    <>
      <DetailShell
        title={`${record.currency} ${record.agreed_price}`}
        subtitle={`${record.listing ?? ''} · ${record.buyer ?? 'Buyer'}`}
        status={record.status.replace(/_/g, ' ')}
        statusTone={record.status_tone}
        editUrl={can.update && !record.is_transferred ? `/brokerage/transactions/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/brokerage/transactions/${record.id}` : undefined}
        backUrl="/brokerage/transactions"
        actions={[
          can.update && !record.is_transferred ? (
            <Button key="funds" variant="secondary" icon={<Banknote className="size-4" />} onClick={() => setRecordingFunds(true)}>
              Record funds
            </Button>
          ) : null,
          can.clearAml && !record.aml_cleared ? (
            <Button key="aml" variant="secondary" icon={<BadgeCheck className="size-4" />} onClick={() => setClearingAml(true)}>
              Clear AML
            </Button>
          ) : null,
          can.transfer && !record.is_transferred ? (
            <Button
              key="transfer"
              variant={blocked ? 'secondary' : 'primary'}
              icon={<KeyRound className="size-4" />}
              disabled={blocked && !can.override}
              onClick={() => setTransferring(true)}
            >
              {blocked ? 'Transfer (blocked)' : 'Transfer ownership'}
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Price', value: <Money amount={record.agreed_price} currency={record.currency} /> },
          { label: 'Deposit', value: record.deposit_amount ? <Money amount={record.deposit_amount} currency={record.currency} /> : '—' },
          { label: 'Deposit cleared', value: <DateText value={record.deposit_cleared_at} /> },
          { label: 'Balance cleared', value: <DateText value={record.balance_cleared_at} /> },
          { label: 'Contract', value: record.contract_type.toUpperCase() },
          { label: 'Escrow', value: record.escrow_agent ?? '—' },
          { label: 'AML', value: record.aml_cleared ? 'Cleared' : 'Outstanding' },
          { label: 'Closing', value: <DateText value={record.expected_closing_on} /> },
          { label: 'Transferred', value: <DateText value={record.ownership_transferred_at} withTime /> },
        ]}
      >
        {!record.is_transferred &&
          (blocked ? <GatePanel gate={gate} /> : <GateCleared label="Funds cleared and AML clear — ownership can transfer" />)}

        {record.notes && (
          <Card>
            <CardHeader>
              <CardTitle>Notes</CardTitle>
            </CardHeader>
            <CardBody>
              <p className="whitespace-pre-wrap text-body text-ink">{record.notes}</p>
            </CardBody>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={recordingFunds}
        onOpenChange={setRecordingFunds}
        title="Record cleared funds"
        description="Cleared means the money has arrived and been reconciled — not that it has been sent."
        footer={
          <>
            <Button variant="secondary" onClick={() => setRecordingFunds(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={funds.processing}
              onClick={() =>
                funds.post(`/brokerage/transactions/${record.id}/funds`, {
                  preserveScroll: true,
                  onSuccess: () => setRecordingFunds(false),
                })
              }
            >
              Record
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Which leg"
            required
            value={funds.data.leg}
            onChange={(event) => funds.setData('leg', event.target.value)}
            options={[
              { value: 'deposit', label: 'Deposit' },
              { value: 'balance', label: 'Balance' },
            ]}
          />
          <Input
            label="Cleared at"
            type="datetime-local"
            required
            value={funds.data.cleared_at}
            error={funds.errors.cleared_at}
            onChange={(event) => funds.setData('cleared_at', event.target.value)}
          />
        </div>
      </Modal>

      <Modal
        open={clearingAml}
        onOpenChange={setClearingAml}
        title="Record AML clearance"
        description="What was checked, against what, and by whom. This is the record a regulator asks for."
        footer={
          <>
            <Button variant="secondary" onClick={() => setClearingAml(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={aml.processing}
              disabled={aml.data.notes.trim().length < 20}
              onClick={() =>
                aml.post(`/brokerage/transactions/${record.id}/aml`, {
                  preserveScroll: true,
                  onSuccess: () => setClearingAml(false),
                })
              }
            >
              Record clearance
            </Button>
          </>
        }
      >
        <Textarea
          label="Screening notes"
          required
          rows={4}
          value={aml.data.notes}
          error={aml.errors.notes}
          help="At least 20 characters."
          onChange={(event) => aml.setData('notes', event.target.value)}
        />
      </Modal>

      <Modal
        open={transferring}
        onOpenChange={setTransferring}
        title="Transfer ownership"
        description="This marks the listing sold and cannot be undone."
        footer={
          <>
            <Button variant="secondary" onClick={() => setTransferring(false)}>
              Cancel
            </Button>
            <Button
              variant={blocked ? 'destructive' : 'primary'}
              loading={transfer.processing}
              disabled={blocked && transfer.data.override_reason.trim().length < 20}
              onClick={() =>
                transfer.post(`/brokerage/transactions/${record.id}/transfer-ownership`, {
                  preserveScroll: true,
                  onSuccess: () => setTransferring(false),
                })
              }
            >
              {blocked ? 'Override and transfer' : 'Transfer ownership'}
            </Button>
          </>
        }
      >
        {blocked ? (
          <Textarea
            label="Override reason"
            required
            rows={3}
            value={transfer.data.override_reason}
            error={transfer.errors.override_reason}
            help="At least 20 characters. Recorded in the Override Register against your name."
            onChange={(event) => transfer.setData('override_reason', event.target.value)}
          />
        ) : (
          <p className="text-body text-ink-soft">Funds have cleared and AML is clear. The listing will be marked sold.</p>
        )}
      </Modal>
    </>
  )
}
