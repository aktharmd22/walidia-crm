import { useState } from 'react'
import { Link, router, useForm } from '@inertiajs/react'
import { Check, Copy, Send, X } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'
import { Modal } from '@/ui/Overlays'
import { Textarea } from '@/ui/Field'
import type { ProposalRow } from '@/pages/Charter/Proposals/Index'

interface Item {
  id: number
  type: string
  category: string | null
  description: string
  quantity: string
  unit: string | null
  unit_price: string
  tax_rate: string
  tax_treatment: string
  tax_amount: string
  line_total: string
  yacht: string | null
}

export default function ProposalShow({
  record,
  timeline = [],
  can,
}: {
  record: ProposalRow & { subtotal: string; tax_amount: string; terms: string | null; items: Item[] }
  timeline?: TimelineEntry[]
  can: { update?: boolean; delete?: boolean }
}) {
  const [declineOpen, setDeclineOpen] = useState(false)
  const decline = useForm({ reason: '' })

  const isOpen = record.status === 'sent' || record.status === 'viewed'

  return (
    <>
      <DetailShell
        title={`${record.reference} · v${record.version}`}
        subtitle={record.client?.name}
        status={record.status}
        statusTone={record.status_tone}
        editUrl={can.update && record.status === 'draft' ? `/charter/proposals/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/charter/proposals/${record.id}` : undefined}
        backUrl="/charter/proposals"
        actions={[
          record.status === 'draft' ? (
            <Button
              key="send"
              variant="primary"
              icon={<Send className="size-4" />}
              onClick={() => router.post(`/charter/proposals/${record.id}/send`)}
            >
              Send to client
            </Button>
          ) : null,
          isOpen ? (
            <Button
              key="accept"
              variant="primary"
              icon={<Check className="size-4" />}
              onClick={() => router.post(`/charter/proposals/${record.id}/accept`)}
            >
              Client accepted
            </Button>
          ) : null,
          isOpen ? (
            <Button key="decline" variant="secondary" icon={<X className="size-4" />} onClick={() => setDeclineOpen(true)}>
              Declined
            </Button>
          ) : null,
        ]}
        menu={[
          {
            key: 'version',
            label: 'Draft a new version',
            icon: <Copy className="size-4" />,
            onSelect: () => router.post(`/charter/proposals/${record.id}/version`),
          },
        ]}
        facts={[
          { label: 'Reference', value: <span className="numeric">{record.reference}</span> },
          { label: 'Version', value: <span className="numeric">v{record.version}</span> },
          { label: 'Valid until', value: <DateText value={record.valid_until} /> },
          {
            label: 'Enquiry',
            value: record.enquiry ? (
              <Link href={record.enquiry.url} className="text-accent hover:underline">
                {record.enquiry.reference}
              </Link>
            ) : (
              '—'
            ),
          },
          { label: 'Subtotal', value: <Money amount={record.subtotal} currency={record.currency} /> },
          { label: 'VAT', value: <Money amount={record.tax_amount} currency={record.currency} /> },
          { label: 'Total', value: <Money amount={record.total} currency={record.currency} /> },
        ]}
        timeline={timeline}
      >
        <Card>
          <CardHeader>
            <CardTitle>What is being offered</CardTitle>
            <Money amount={record.total} currency={record.currency} className="text-h2 text-ink" />
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
                      <span className="block text-small text-ink-faint">
                        {item.yacht ?? item.category?.replace(/_/g, ' ') ?? item.type}
                        {item.tax_treatment !== 'standard' && ` · ${item.tax_treatment.replace(/_/g, ' ')}`}
                      </span>
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

        {record.terms && (
          <Card>
            <CardHeader>
              <CardTitle>Terms</CardTitle>
            </CardHeader>
            <div className="px-5 py-4">
              <p className="whitespace-pre-line text-body text-ink-soft">{record.terms}</p>
            </div>
          </Card>
        )}
      </DetailShell>

      <Modal
        open={declineOpen}
        onOpenChange={setDeclineOpen}
        title="Mark this proposal declined"
        description="The reason feeds pipeline reporting, so keep it specific."
        footer={
          <>
            <Button variant="secondary" onClick={() => setDeclineOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              loading={decline.processing}
              onClick={() =>
                decline.post(`/charter/proposals/${record.id}/decline`, {
                  preserveScroll: true,
                  onSuccess: () => setDeclineOpen(false),
                })
              }
            >
              Mark declined
            </Button>
          </>
        }
      >
        <Textarea
          label="Why did they decline?"
          value={decline.data.reason}
          error={decline.errors.reason}
          onChange={(event) => decline.setData('reason', event.target.value)}
        />
      </Modal>
    </>
  )
}
