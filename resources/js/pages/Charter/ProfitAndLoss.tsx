import { Head, Link, router } from '@inertiajs/react'
import { PageHeader, Pagination } from '@/components/shell/Page'
import { Card, CardBody, EmptyState, Money, Percent } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated } from '@/types'

interface Row {
  id: number
  reference: string
  booking: string | null
  client: string | null
  yacht: string | null
  phase: string
  offer: string
  cost: string
  profit: string
  margin: string
  currency: string
  url: string
}

/**
 * Charter P&L. Reads actuals where they exist and falls back to invoiced, so a
 * charter that has not been reconciled yet still shows a defensible number.
 */
export default function ProfitAndLoss({
  rows,
  totals,
}: {
  rows: Paginated<Row>
  totals: { offer: number; cost: number; profit: number }
}) {
  return (
    <>
      <Head title="Charter P&L" />

      <PageHeader title="Charter P&L" description="Per charter, from the phase that best reflects reality." />

      <div className="grid gap-4 sm:grid-cols-3">
        <Card>
          <CardBody>
            <p className="text-h3 text-ink-soft">Total offer</p>
            <p className="mt-2 text-display text-ink">
              <Money amount={totals.offer} />
            </p>
          </CardBody>
        </Card>
        <Card>
          <CardBody>
            <p className="text-h3 text-ink-soft">Total cost</p>
            <p className="mt-2 text-display text-ink">
              <Money amount={totals.cost} />
            </p>
          </CardBody>
        </Card>
        <Card className="border-s-2 border-success">
          <CardBody>
            <p className="text-h3 text-ink-soft">Total profit</p>
            <p className="mt-2 text-display text-ink">
              <Money amount={totals.profit} />
            </p>
          </CardBody>
        </Card>
      </div>

      <Card>
        {rows.data.length === 0 ? (
          <EmptyState title="Nothing to report yet" description="Cost sheets appear here as charters are priced." />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-deck">
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Charter</th>
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Phase</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Offer</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Cost</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Profit</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Margin</th>
                </tr>
              </thead>
              <tbody>
                {rows.data.map((row) => (
                  <tr key={row.id} className="border-b border-line last:border-0 hover:bg-deck">
                    <td className="px-4 py-3">
                      <Link href={row.url} className="text-h3 text-ink hover:text-accent">
                        {row.yacht ?? row.reference}
                      </Link>
                      <span className="block text-small text-ink-faint">
                        {row.client} · {row.booking}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <StatusPill tone={row.phase === 'actual' ? 'success' : 'info'}>{row.phase}</StatusPill>
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={row.offer} currency={row.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={row.cost} currency={row.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={row.profit} currency={row.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Percent value={row.margin} />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </Card>

      <Pagination page={rows} onNavigate={(url) => router.visit(url, { preserveScroll: true })} />
    </>
  )
}
