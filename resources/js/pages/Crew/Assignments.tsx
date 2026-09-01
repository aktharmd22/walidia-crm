import { Head, router } from '@inertiajs/react'
import { PageHeader, Pagination } from '@/components/shell/Page'
import { GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { GateResult, Paginated } from '@/types'

interface Row {
  id: number
  crew: string | null
  role: string | null
  booking: string | null
  starts_at: string
  status: string
  dispatched_at: string | null
  gate: GateResult
}

/**
 * Who is going where — and why anyone who cannot be dispatched yet cannot be.
 */
export default function CrewAssignments({ rows, can }: { rows: Paginated<Row>; can: { dispatch?: boolean } }) {
  return (
    <>
      <Head title="Crew assignments" />

      <PageHeader
        title="Crew assignments"
        description="Dispatch needs Operational Release and valid documents. Both are checked here before anyone travels."
      />

      <Card>
        {rows.data.length === 0 ? (
          <EmptyState title="No assignments" description="Assign crew from a confirmed booking." />
        ) : (
          <ul className="divide-y divide-line">
            {rows.data.map((row) => (
              <li key={row.id} className="flex flex-col gap-3 px-5 py-4">
                <div className="flex flex-wrap items-center gap-3">
                  <span className="min-w-0 flex-1">
                    <span className="block text-h3 text-ink">{row.crew}</span>
                    <span className="block text-small text-ink-faint">
                      {row.role} · {row.booking} · <DateText value={row.starts_at} withTime />
                    </span>
                  </span>

                  {row.dispatched_at ? (
                    <StatusPill tone="success">Dispatched</StatusPill>
                  ) : (
                    <Button
                      size="sm"
                      variant={row.gate.verdict === 'block' ? 'secondary' : 'primary'}
                      disabled={row.gate.verdict === 'block' || !can.dispatch}
                      onClick={() => router.post(`/crew/assignments/${row.id}/dispatch`, {}, { preserveScroll: true })}
                    >
                      Dispatch
                    </Button>
                  )}
                </div>

                <GatePanel gate={row.gate} />
              </li>
            ))}
          </ul>
        )}
      </Card>

      <Pagination page={rows} onNavigate={(url) => router.visit(url, { preserveScroll: true })} />
    </>
  )
}
