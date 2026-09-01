import { useState, type FormEvent } from 'react'
import { Head, useForm } from '@inertiajs/react'
import { Plus, Trash2 } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, Money } from '@/ui/Primitives'
import { Input, Select } from '@/ui/Field'
import type { FormValues } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

interface Line {
  description_en: string
  category: string
  quantity: string
  unit_price: string
}

/**
 * A draft invoice. It takes its gapless number only when issued.
 */
export default function InvoiceCreate({
  clients = []
}: {
  clients?: Option[]
}) {
  const [lines, setLines] = useState<Line[]>([{ description_en: '', category: 'yacht_fee', quantity: '1', unit_price: '' }])

  const form = useForm<FormValues>({
    type: 'tax_invoice',
    client_id: '',
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    place_of_supply: 'United Arab Emirates',
    tax_treatment: 'standard',
    currency: 'AED',
    notes: '',
  })

  const subtotal = lines.reduce((sum, line) => sum + Number(line.quantity || 0) * Number(line.unit_price || 0), 0)

  function update(index: number, patch: Partial<Line>) {
    setLines((current) => current.map((line, position) => (position === index ? { ...line, ...patch } : line)))
  }

  function submit(event: FormEvent) {
    event.preventDefault()
    form.transform((data) => ({ ...data, items: lines }))
    form.post('/finance/invoices')
  }

  return (
    <>
      <Head title="New invoice" />

      <PageHeader title="New invoice" description="Drafts are fully editable. Issuing allocates the number and freezes the document." />

      <form onSubmit={submit} className="grid gap-5 xl:grid-cols-[1fr_320px]">
        <div className="flex flex-col gap-5">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardBody className="grid gap-3 md:grid-cols-2">
              <Select
                label="Client"
                value={String(form.data.client_id ?? '')}
                error={form.errors.client_id as string | undefined}
                onChange={(event) => form.setData('client_id', event.target.value)}
                options={clients}
              />
              <Select
                label="Type"
                required
                value={String(form.data.type ?? 'tax_invoice')}
                onChange={(event) => form.setData('type', event.target.value)}
                options={[
                  { value: 'tax_invoice', label: 'Tax invoice' },
                  { value: 'proforma', label: 'Proforma' },
                ]}
              />
              <Input
                label="Issue date"
                type="date"
                value={String(form.data.issue_date ?? '')}
                onChange={(event) => form.setData('issue_date', event.target.value)}
              />
              <Input
                label="Due date"
                type="date"
                value={String(form.data.due_date ?? '')}
                onChange={(event) => form.setData('due_date', event.target.value)}
              />
              <Select
                label="VAT treatment"
                required
                value={String(form.data.tax_treatment ?? 'standard')}
                onChange={(event) => form.setData('tax_treatment', event.target.value)}
                options={[
                  { value: 'standard', label: 'Standard rated (5%)' },
                  { value: 'zero_rated', label: 'Zero rated' },
                  { value: 'out_of_scope', label: 'Outside the scope of UAE VAT' },
                  { value: 'reverse_charge', label: 'Reverse charge' },
                ]}
              />
              <Input
                label="Place of supply"
                value={String(form.data.place_of_supply ?? '')}
                onChange={(event) => form.setData('place_of_supply', event.target.value)}
              />
            </CardBody>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Lines</CardTitle>
              <Button
                type="button"
                variant="secondary"
                size="sm"
                icon={<Plus className="size-4" />}
                onClick={() => setLines((current) => [...current, { description_en: '', category: 'other_revenue', quantity: '1', unit_price: '' }])}
              >
                Add line
              </Button>
            </CardHeader>
            <CardBody className="flex flex-col gap-4">
              {lines.map((line, index) => (
                <div key={index} className="grid gap-3 border-b border-line pb-4 last:border-0 last:pb-0 md:grid-cols-[2fr_1fr_1fr_auto]">
                  <Input
                    label="Description"
                    required
                    value={line.description_en}
                    onChange={(event) => update(index, { description_en: event.target.value })}
                  />
                  <Input
                    label="Quantity"
                    numeric
                    required
                    value={line.quantity}
                    onChange={(event) => update(index, { quantity: event.target.value })}
                  />
                  <Input
                    label="Unit price"
                    numeric
                    required
                    value={line.unit_price}
                    onChange={(event) => update(index, { unit_price: event.target.value })}
                  />
                  <div className="flex items-end pb-2">
                    <Button
                      type="button"
                      variant="ghost"
                      icon={<Trash2 className="size-4" />}
                      onClick={() => setLines((current) => current.filter((_, position) => position !== index))}
                      aria-label="Remove line"
                    >
                      {''}
                    </Button>
                  </div>
                </div>
              ))}
            </CardBody>
          </Card>
        </div>

        <div className="flex flex-col gap-5">
          <Card>
            <CardBody className="flex flex-col gap-3">
              <div className="flex items-center justify-between">
                <span className="text-h3 text-ink-soft">Subtotal</span>
                <Money amount={subtotal} />
              </div>
              <p className="text-small text-ink-faint">
                VAT is applied per line by treatment when this is saved — the server, not the browser, decides the tax.
              </p>
              <Button type="submit" variant="primary" block loading={form.processing}>
                Create draft
              </Button>
            </CardBody>
          </Card>
        </div>
      </form>
    </>
  )
}
