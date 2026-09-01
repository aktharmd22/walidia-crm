import { useState } from 'react'
import { Head, Link, router, useForm } from '@inertiajs/react'
import { ArrowRight, Lock, Plus, Trash2 } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, EmptyState, Money, Percent } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Drawer, Modal, Tabs } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import { cn } from '@/lib/cn'

interface Line {
  id: number
  phase: string
  section: string
  category: string
  description: string | null
  quantity: string
  unit_price: string
  amount: string
  tax_treatment: string
  tax_amount: string
}

interface VarianceRow {
  category: string
  section: string
  quoted: number
  actual: number
  variance: number
}

interface Sheet {
  id: number
  reference: string
  status: string
  currency: string
  total_offer: string
  total_cost: string
  total_profit: string
  margin_pct: string
  effective_phase: string
  is_closed: boolean
  writable_phases: string[]
  booking?: { id: number; reference: string; url: string } | null
  lines: Line[]
}

/**
 * The Cost & Offer sheet.
 *
 * One artifact with three phases — quoted, invoiced, actual — so the P&L is a
 * variance view rather than three documents that disagree (D-011).
 */
export default function CostSheetShow({
  record,
  variance = [],
  categories,
  can,
}: {
  record: Sheet
  variance?: VarianceRow[]
  categories: { revenue: Record<string, string>; cost: Record<string, string> }
  can: { close?: boolean; override?: boolean }
}) {
  const [phase, setPhase] = useState(record.effective_phase)
  const [lineOpen, setLineOpen] = useState(false)
  const [closeOpen, setCloseOpen] = useState(false)

  const line = useForm({
    phase,
    section: 'revenue',
    category: 'yacht_fee',
    description: '',
    quantity: '1',
    unit_price: '',
  })

  const closing = useForm({ override_reason: '' })
  const writable = record.writable_phases.includes(phase) && !record.is_closed
  const lines = record.lines.filter((row) => row.phase === phase)

  const revenue = lines.filter((row) => row.section === 'revenue')
  const costs = lines.filter((row) => row.section === 'cost')

  return (
    <>
      <Head title={`Cost sheet ${record.reference}`} />

      <PageHeader
        title={`Cost sheet ${record.reference}`}
        description={
          record.booking ? (
            <>
              Charter <Link href={record.booking.url} className="text-accent hover:underline">{record.booking.reference}</Link>
            </>
          ) as unknown as string : undefined
        }
        actions={
          <>
            <StatusPill tone={record.is_closed ? 'neutral' : 'info'}>{record.status}</StatusPill>
            {!record.is_closed && can.close && (
              <Button variant="primary" icon={<Lock className="size-4" />} onClick={() => setCloseOpen(true)}>
                Close sheet
              </Button>
            )}
          </>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Figure label="Total offer" value={record.total_offer} currency={record.currency} tone="info" />
        <Figure label="Total cost" value={record.total_cost} currency={record.currency} tone="attention" />
        <Figure label="Profit" value={record.total_profit} currency={record.currency} tone="success" />
        <Card>
          <CardBody>
            <p className="text-h3 text-ink-soft">Margin</p>
            <p className="mt-2 text-display text-ink">
              <Percent value={record.margin_pct} />
            </p>
          </CardBody>
        </Card>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <Tabs
          value={phase}
          onValueChange={(next) => {
            setPhase(next)
            line.setData('phase', next)
          }}
          items={[
            { value: 'quoted', label: 'Quoted' },
            { value: 'invoiced', label: 'Invoiced' },
            { value: 'actual', label: 'Actual' },
          ]}
        />

        {writable && (
          <>
            <Button variant="primary" icon={<Plus className="size-4" />} onClick={() => setLineOpen(true)}>
              Add line
            </Button>
            {phase !== 'quoted' && (
              <Button
                variant="secondary"
                icon={<ArrowRight className="size-4" />}
                onClick={() =>
                  router.post(`/charter/cost-sheets/${record.id}/copy-phase`, {
                    from: phase === 'invoiced' ? 'quoted' : 'invoiced',
                    to: phase,
                  })
                }
              >
                Copy from {phase === 'invoiced' ? 'quoted' : 'invoiced'}
              </Button>
            )}
          </>
        )}

        {!writable && !record.is_closed && (
          <span className="text-small text-ink-faint">You can read this phase but not change it.</span>
        )}
      </div>

      <div className="grid gap-5 xl:grid-cols-2">
        <LineTable
          title="Revenue"
          lines={revenue}
          currency={record.currency}
          writable={writable}
          sheetId={record.id}
          labels={categories.revenue}
        />
        <LineTable
          title="Costs"
          lines={costs}
          currency={record.currency}
          writable={writable}
          sheetId={record.id}
          labels={categories.cost}
        />
      </div>

      {variance.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Quoted against actual</CardTitle>
            <span className="text-small text-ink-faint">Where the charter drifted from the quote</span>
          </CardHeader>
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-deck">
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Category</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Quoted</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Actual</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Variance</th>
                </tr>
              </thead>
              <tbody>
                {variance.map((row) => (
                  <tr key={`${row.section}-${row.category}`} className="border-b border-line last:border-0">
                    <td className="px-4 py-2 text-body text-ink">
                      {row.category.replace(/_/g, ' ')}
                      <span className="ms-2 text-micro text-ink-faint">{row.section}</span>
                    </td>
                    <td className="px-4 py-2 text-end">
                      <Money amount={row.quoted} currency={record.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-2 text-end">
                      <Money amount={row.actual} currency={record.currency} withCurrency={false} />
                    </td>
                    <td
                      className={cn(
                        'px-4 py-2 text-end numeric',
                        row.variance < 0 ? 'text-danger' : row.variance > 0 ? 'text-success' : 'text-ink-soft',
                      )}
                    >
                      {row.variance > 0 ? '+' : ''}
                      {row.variance.toLocaleString('en-AE', { minimumFractionDigits: 2 })}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      )}

      <Drawer
        open={lineOpen}
        onOpenChange={setLineOpen}
        title="Add a line"
        description={`Added to the ${phase} phase.`}
        footer={
          <>
            <Button variant="secondary" onClick={() => setLineOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={line.processing}
              onClick={() =>
                line.post(`/charter/cost-sheets/${record.id}/lines`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    line.reset()
                    line.setData('phase', phase)
                    setLineOpen(false)
                  },
                })
              }
            >
              Add line
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Section"
            required
            value={line.data.section}
            onChange={(event) => line.setData('section', event.target.value)}
            options={[
              { value: 'revenue', label: 'Revenue — what the client pays' },
              { value: 'cost', label: 'Cost — what we pay out' },
            ]}
          />
          <Select
            label="Category"
            required
            value={line.data.category}
            error={line.errors.category}
            onChange={(event) => line.setData('category', event.target.value)}
            options={Object.entries(line.data.section === 'revenue' ? categories.revenue : categories.cost).map(
              ([value, label]) => ({ value, label }),
            )}
          />
          <Textarea
            label="Description"
            value={line.data.description}
            onChange={(event) => line.setData('description', event.target.value)}
          />
          <Input
            label="Quantity"
            numeric
            required
            value={line.data.quantity}
            error={line.errors.quantity}
            onChange={(event) => line.setData('quantity', event.target.value)}
          />
          <Input
            label="Unit price"
            numeric
            required
            value={line.data.unit_price}
            error={line.errors.unit_price}
            help="VAT is applied by treatment; deposits and tips are out of scope."
            onChange={(event) => line.setData('unit_price', event.target.value)}
          />
        </div>
      </Drawer>

      <Modal
        open={closeOpen}
        onOpenChange={setCloseOpen}
        title="Close this cost sheet?"
        description="Closing finalises the P&L for this charter. It requires a receipt for every cleared payment."
        footer={
          <>
            <Button variant="secondary" onClick={() => setCloseOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={closing.processing}
              onClick={() =>
                closing.post(`/charter/cost-sheets/${record.id}/close`, {
                  preserveScroll: true,
                  onSuccess: () => setCloseOpen(false),
                })
              }
            >
              Close sheet
            </Button>
          </>
        }
      >
        {can.override && (
          <Textarea
            label="Override reason (only if the gate blocks it)"
            value={closing.data.override_reason}
            onChange={(event) => closing.setData('override_reason', event.target.value)}
            help="Leave empty unless you are deliberately closing past a blocked gate."
          />
        )}
      </Modal>
    </>
  )
}

function Figure({
  label,
  value,
  currency,
  tone,
}: {
  label: string
  value: string
  currency: string
  tone: 'info' | 'attention' | 'success'
}) {
  const border = { info: 'border-info', attention: 'border-attention', success: 'border-success' }[tone]

  return (
    <Card className={cn('border-s-2', border)}>
      <CardBody>
        <p className="text-h3 text-ink-soft">{label}</p>
        <p className="mt-2 text-display text-ink">
          <Money amount={value} currency={currency} />
        </p>
      </CardBody>
    </Card>
  )
}

function LineTable({
  title,
  lines,
  currency,
  writable,
  sheetId,
  labels,
}: {
  title: string
  lines: Line[]
  currency: string
  writable: boolean
  sheetId: number
  labels: Record<string, string>
}) {
  const total = lines.reduce((sum, row) => sum + Number(row.amount), 0)

  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
        <Money amount={total} currency={currency} className="text-h2 text-ink" />
      </CardHeader>
      {lines.length === 0 ? (
        <EmptyState title={`No ${title.toLowerCase()} lines`} description="Add lines for this phase." />
      ) : (
        <ul className="divide-y divide-line">
          {lines.map((row) => (
            <li key={row.id} className="flex items-center gap-3 px-5 py-3">
              <span className="min-w-0 flex-1">
                <span className="block text-h3 text-ink">{labels[row.category] ?? row.category}</span>
                <span className="block text-small text-ink-faint">
                  {row.description || `${row.quantity} × ${row.unit_price}`}
                  {row.tax_treatment !== 'standard' && ` · ${row.tax_treatment.replace(/_/g, ' ')}`}
                </span>
              </span>
              <Money amount={row.amount} currency={currency} withCurrency={false} />
              {writable && (
                <button
                  type="button"
                  onClick={() => router.delete(`/charter/cost-sheets/${sheetId}/lines/${row.id}`, { preserveScroll: true })}
                  className="rounded-pill p-2 text-ink-faint hover:bg-danger-bg hover:text-danger"
                  aria-label="Remove line"
                >
                  <Trash2 className="size-4" aria-hidden />
                </button>
              )}
            </li>
          ))}
        </ul>
      )}
    </Card>
  )
}
