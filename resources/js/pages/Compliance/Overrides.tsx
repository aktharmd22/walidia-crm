import { Head } from '@inertiajs/react'
import { ShieldAlert } from 'lucide-react'
import { PageHeader, Pagination } from '@/components/shell/Page'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { router } from '@inertiajs/react'
import type { Paginated } from '@/types'

interface OverrideRow {
  id: number
  rule: string
  rule_key: string | null
  subject_type: string
  subject_id: number
  user: string
  reason: string
  failed_conditions: { message?: string }[] | null
  ip_address: string | null
  created_at: string | null
}

/**
 * The Override Register.
 *
 * Read-only for everybody, including Admin. There is no route in this
 * application that edits or deletes a row here — that is the point of it.
 */
export default function Overrides({ rows }: { rows: Paginated<OverrideRow> }) {
  return (
    <>
      <Head title="Override Register" />

      <PageHeader
        title="Override Register"
        description="Every hard gate someone chose to walk past, with the reason they gave. Permanent and read-only."
      />

      <Card>
        {rows.data.length === 0 ? (
          <EmptyState
            icon={<ShieldAlert className="size-5" aria-hidden />}
            title="No overrides recorded"
            description="When an Admin proceeds past a blocked gate, it is recorded here with their reason."
          />
        ) : (
          <ul className="divide-y divide-line">
            {rows.data.map((override) => (
              <li key={override.id} className="flex flex-col gap-2 px-5 py-4">
                <div className="flex flex-wrap items-center gap-3">
                  <StatusPill tone="danger">Override</StatusPill>
                  <span className="text-h3 text-ink">{override.rule}</span>
                  <span className="text-small text-ink-faint">
                    {override.subject_type} #{override.subject_id}
                  </span>
                  <span className="ms-auto text-small text-ink-faint">
                    {override.user} · <DateText value={override.created_at} withTime />
                  </span>
                </div>

                <p className="text-body text-ink-soft">“{override.reason}”</p>

                {override.failed_conditions && override.failed_conditions.length > 0 && (
                  <ul className="flex flex-col gap-1">
                    {override.failed_conditions.map((condition, index) => (
                      <li key={index} className="text-small text-ink-faint">
                        Bypassed: {condition.message ?? 'condition'}
                      </li>
                    ))}
                  </ul>
                )}

                {override.ip_address && (
                  <p className="numeric text-micro text-ink-faint">{override.ip_address}</p>
                )}
              </li>
            ))}
          </ul>
        )}
      </Card>

      <Pagination page={rows} onNavigate={(url) => router.visit(url, { preserveScroll: true })} />
    </>
  )
}
