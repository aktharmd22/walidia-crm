import { Head, router } from '@inertiajs/react'
import { PageHeader, Pagination } from '@/components/shell/Page'
import { Card, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { Paginated } from '@/types'

interface Row {
  id: number
  reference: string | null
  crew: string | null
  booking: string | null
  days: string
  tips_amount: string
  net: string
  currency: string
  status: string
}

/** Per-charter pay and tips. A deal does not close while any of this is unpaid. */
export default function CrewPayouts({ rows }: { rows: Paginated<Row> }) {
  return (
    <>
      <Head title="Crew payouts" />

      <PageHeader title="Crew payouts" description="Day rates and tips per charter. Payroll itself is out of scope." />

      <Card>
        {rows.data.length === 0 ? (
          <EmptyState title="No payouts yet" description="Payouts are raised from completed charters." />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-deck">
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Crew</th>
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Charter</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Days</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Tips</th>
                  <th className="px-4 py-3 text-end text-micro text-ink-faint">Net</th>
                  <th className="px-4 py-3 text-start text-micro text-ink-faint">Status</th>
                </tr>
              </thead>
              <tbody>
                {rows.data.map((row) => (
                  <tr key={row.id} className="border-b border-line last:border-0">
                    <td className="px-4 py-3 text-body text-ink">{row.crew}</td>
                    <td className="px-4 py-3 text-small text-ink-faint">{row.booking ?? '—'}</td>
                    <td className="px-4 py-3 text-end">
                      <Num value={row.days} fractionDigits={1} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={row.tips_amount} currency={row.currency} withCurrency={false} />
                    </td>
                    <td className="px-4 py-3 text-end">
                      <Money amount={row.net} currency={row.currency} />
                    </td>
                    <td className="px-4 py-3">
                      <StatusPill tone={row.status === 'paid' ? 'success' : 'warning'}>{row.status}</StatusPill>
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
