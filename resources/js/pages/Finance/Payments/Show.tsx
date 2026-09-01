import { router } from '@inertiajs/react'
import { Check, Link2 } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardHeader, CardTitle, DateText, EmptyState, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { PaymentRow } from '@/pages/Finance/Payments/Index'

interface Allocation {
  id: number
  amount: string
  invoice: string | null
  invoice_id: number | null
}

/**
 * Clearing this payment is what unlocks Operational Release, so the screen
 * says so plainly rather than hiding it behind a status word.
 */
export default function PaymentShow({
  record,
  openInvoices = [],
  can,
}: {
  record: PaymentRow & {
    amount_aed: string
    reconciled_at: string | null
    bank_charge_amount: string | null
    notes: string | null
    allocations?: Allocation[]
  }
  openInvoices?: { id: number; reference: string; amount_due: string; currency: string }[]
  can: { confirm?: boolean; reconcile?: boolean; update?: boolean }
}) {
  return (
    <DetailShell
      title={record.reference ?? 'Payment'}
      subtitle={record.client?.name}
      status={record.status}
      statusTone={record.status_tone}
      editUrl={can.update ? `/finance/payments/${record.id}/edit` : undefined}
      backUrl="/finance/payments"
      actions={[
        can.confirm && !record.is_cleared ? (
          <Button
            key="clear"
            variant="primary"
            icon={<Check className="size-4" />}
            onClick={() => router.post(`/finance/payments/${record.id}/confirm-deposit`)}
          >
            Confirm cleared
          </Button>
        ) : null,
        can.reconcile && record.is_cleared && !record.reconciled_at ? (
          <Button key="reconcile" variant="secondary" onClick={() => router.post(`/finance/payments/${record.id}/reconcile`)}>
            Mark reconciled
          </Button>
        ) : null,
      ]}
      facts={[
        { label: 'Amount', value: <Money amount={record.amount} currency={record.currency} /> },
        { label: 'In AED', value: <Money amount={record.amount_aed} /> },
        { label: 'Method', value: record.method.replace(/_/g, ' ') },
        { label: 'Reference', value: <span className="numeric">{record.reference ?? '—'}</span> },
        { label: 'Received', value: <DateText value={record.received_at} withTime /> },
        { label: 'Cleared', value: record.cleared_at ? <DateText value={record.cleared_at} withTime /> : 'Not cleared' },
        { label: 'Reconciled', value: record.reconciled_at ? <DateText value={record.reconciled_at} /> : 'No' },
        { label: 'Bank charge', value: record.bank_charge_amount ? <Money amount={record.bank_charge_amount} /> : '—' },
        { label: 'Unallocated', value: <Money amount={record.unallocated} currency={record.currency} /> },
      ]}
    >
      {!record.is_cleared && (
        <p className="rounded-card border border-warning bg-warning-bg px-4 py-3 text-small text-warning">
          Until this is confirmed cleared, it does not count towards the deposit — and Operational Release stays blocked.
        </p>
      )}

      <Card>
        <CardHeader>
          <CardTitle>What this settled</CardTitle>
        </CardHeader>
        {(record.allocations ?? []).length === 0 ? (
          <EmptyState
            icon={<Link2 className="size-5" aria-hidden />}
            title="Not allocated yet"
            description="Allocate it to an invoice or a scheduled instalment so the balance updates."
          />
        ) : (
          <ul className="divide-y divide-line">
            {record.allocations?.map((allocation) => (
              <li key={allocation.id} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="text-body text-ink">{allocation.invoice ?? 'Scheduled instalment'}</span>
                <Money amount={allocation.amount} currency={record.currency} />
              </li>
            ))}
          </ul>
        )}
      </Card>

      {openInvoices.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Open invoices for this client</CardTitle>
          </CardHeader>
          <ul className="divide-y divide-line">
            {openInvoices.map((invoice) => (
              <li key={invoice.id} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="numeric text-body text-ink">{invoice.reference}</span>
                <span className="flex items-center gap-3">
                  <Money amount={invoice.amount_due} currency={invoice.currency} />
                  <StatusPill tone="warning">Outstanding</StatusPill>
                </span>
              </li>
            ))}
          </ul>
        </Card>
      )}
    </DetailShell>
  )
}
