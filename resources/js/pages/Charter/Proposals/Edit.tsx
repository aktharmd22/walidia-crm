import { useState, type FormEvent } from 'react'
import { Head, useForm } from '@inertiajs/react'
import { Plus, Trash2 } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, Money } from '@/ui/Primitives'
import { Input, Textarea } from '@/ui/Field'
import type { FormValues } from '@/components/crud/ResourceForm'

interface Line {
  description_en: string
  category: string
  quantity: string
  unit_price: string
}

/**
 * Editing a draft proposal. Once sent, it is versioned instead.
 */
export default function ProposalEdit({
  record
}: {
  record: { id: number; reference: string; currency: string; valid_until: string | null; terms: string | null; items?: { description: string; quantity: string; unit_price: string; category: string | null }[] }
}) {
  const [lines, setLines] = useState<Line[]>(record.items?.map((item) => ({
      description_en: item.description,
      category: item.category ?? 'other_revenue',
      quantity: String(item.quantity),
      unit_price: String(item.unit_price),
    })) ?? [])

  const form = useForm<FormValues>({
    valid_until: record.valid_until ?? '',
    terms: record.terms ?? '',
  })

  const subtotal = lines.reduce((sum, line) => sum + Number(line.quantity || 0) * Number(line.unit_price || 0), 0)

  function update(index: number, patch: Partial<Line>) {
    setLines((current) => current.map((line, position) => (position === index ? { ...line, ...patch } : line)))
  }

  function submit(event: FormEvent) {
    event.preventDefault()
    form.transform((data) => ({ ...data, items: lines }))
    form.put('/charter/proposals')
  }

  return (
    <>
      <Head title="Edit proposal" />

      <PageHeader title="Edit proposal" description="Only a draft can be edited — a sent proposal gets a new version." />

      <form onSubmit={submit} className="grid gap-5 xl:grid-cols-[1fr_320px]">
        <div className="flex flex-col gap-5">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardBody className="grid gap-3 md:grid-cols-2">
              <Input
                label="Valid until"
                type="date"
                value={String(form.data.valid_until ?? '')}
                onChange={(event) => form.setData('valid_until', event.target.value)}
              />
              <div className="md:col-span-2">
                <Textarea
                  label="Terms"
                  value={String(form.data.terms ?? '')}
                  onChange={(event) => form.setData('terms', event.target.value)}
                />
              </div>
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
                Save proposal
              </Button>
            </CardBody>
          </Card>
        </div>
      </form>
    </>
  )
}
