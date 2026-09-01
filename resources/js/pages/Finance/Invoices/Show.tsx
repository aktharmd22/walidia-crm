import { useState } from 'react'
import { router, useForm } from '@inertiajs/react'
import { FileCheck, Receipt as ReceiptIcon, XCircle } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Textarea } from '@/ui/Field'
import type { InvoiceRow } from '@/pages/Finance/Invoices/Index'

interface Item {
  id: number
  description: string
  quantity: string
  unit_price: string
  tax_rate: string
  tax_treatment: string
  tax_amount: string
  line_total: string
}

/**
 * An issued invoice is evidence: it can be voided and credited, never edited.
 */
export default function InvoiceShow({
  record,
  cleared,
  can,
}: {
  record: InvoiceRow & {
    subtotal: string
    tax_amount: string
    amount_paid: string
    supplier_trn: string | null
    place_of_supply: string | null
    notes: string | null
    void_reason: string | null
    is_issued: boolean
    is_editable: boolean
    items?: Item[]
  }
  cleared: number
  can: { update?: boolean; issue?: boolean; void?: boolean; credit?: boolean }
}) {
  const [voidOpen, setVoidOpen] = useState(false)
  const voiding = useForm({ reason: '' })

  return (
    <>
      <DetailShell
        title={record.reference ?? 'Draft invoice'}
        subtitle={record.client?.name}
        status={record.is_overdue ? 'Overdue' : record.status.replace('_', ' ')}
        statusTone={record.status_tone}
        editUrl={record.is_editable && can.update ? `/finance/invoices/${record.id}/edit` : undefined}
        backUrl="/finance/invoices"
        actions={[
          can.issue && !record.is_issued ? (
            <Button
              key="issue"
              variant="primary"
              icon={<FileCheck className="size-4" />}
              onClick={() => router.post(`/finance/invoices/${record.id}/issue`)}
            >
              Issue invoice
            </Button>
          ) : null,
          can.void && record.is_issued ? (
            <Button key="void" variant="secondary" icon={<XCircle className="size-4" />} onClick={() => setVoidOpen(true)}>
              Void
            </Button>
          ) : null,
          can.credit && record.is_issued ? (
            <Button
              key="credit"
              variant="secondary"
              icon={<ReceiptIcon className="size-4" />}
              onClick={() => router.post(`/finance/invoices/${record.id}/credit-note`)}
            >
              Credit note
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Number', value: <span className="numeric">{record.reference ?? 'Allocated on issue'}</span> },
          { label: 'Type', value: record.type.replace('_', ' ') },
          { label: 'Issued', value: <DateText value={record.issue_date} /> },
          { label: 'Due', value: <DateText value={record.due_date} /> },
          { label: 'Place of supply', value: record.place_of_supply ?? '—' },
          { label: 'VAT treatment', value: record.tax_treatment.replace(/_/g, ' ') },
          { label: 'Supplier TRN', value: <span className="numeric">{record.supplier_trn ?? 'Not set'}</span> },
          { label: 'Subtotal', value: <Money amount={record.subtotal} currency={record.currency} /> },
          { label: 'VAT', value: <Money amount={record.tax_amount} currency={record.currency} /> },
          { label: 'Total', value: <Money amount={record.total} currency={record.currency} /> },
          { label: 'Cleared', value: <Money amount={cleared} currency={record.currency} /> },
          { label: 'Outstanding', value: <Money amount={record.amount_due} currency={record.currency} /> },
        ]}
      >
        <Card>
          <CardHeader>
            <CardTitle>Lines</CardTitle>
          </CardHeader>
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-deck">
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Description</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Qty</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Unit</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">VAT</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Total</th>
                </tr>
              </thead>
              <tbody>
                {record.items?.map((item) => (
                  <tr key={item.id} className="border-b border-line last:border-0">
                    <td className="px-4 py-3">
                      <span className="block text-body text-ink">{item.description}</span>
                      {item.tax_treatment !== 'standard' && (
                        <span className="block text-small text-ink-faint">{item.tax_treatment.replace(/_/g, ' ')}</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-end numeric">{item.quantity}</td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={item.unit_price} currency={record.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={item.tax_amount} currency={record.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={item.line_total} currency={record.currency} withCurrency={false} />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>

        {record.void_reason && (
          <p className="rounded-card border border-danger bg-danger-bg px-4 py-3 text-small text-danger">
            Voided: {record.void_reason}
          </p>
        )}
      </DetailShell>

      <Modal
        open={voidOpen}
        onOpenChange={setVoidOpen}
        title="Void this invoice?"
        description="The number stays used — voiding never reissues it. Raise a credit note if money has already moved."
        footer={
          <>
            <Button variant="secondary" onClick={() => setVoidOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              loading={voiding.processing}
              onClick={() =>
                voiding.post(`/finance/invoices/${record.id}/void`, {
                  preserveScroll: true,
                  onSuccess: () => setVoidOpen(false),
                })
              }
            >
              Void invoice
            </Button>
          </>
        }
      >
        <Textarea
          label="Reason"
          required
          value={voiding.data.reason}
          error={voiding.errors.reason}
          onChange={(event) => voiding.setData('reason', event.target.value)}
        />
      </Modal>
    </>
  )
}
