import { Head, Link, router } from '@inertiajs/react'
import { Copy } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, EmptyState, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { LeadRow } from '@/pages/Leads/Index'

interface Pair {
  lead: LeadRow
  match: { id: number; name: string; reference: string; url: string }
  score: number
  reason: string
}

/**
 * Probable duplicates, never merged automatically: families share mobile
 * numbers and companies share email domains, so a person decides (Q9).
 */
export default function LeadDuplicates({ pairs = [] }: { pairs?: Pair[] }) {
  return (
    <>
      <Head title="Duplicates" />

      <PageHeader
        title="Duplicates"
        description="Leads that look like someone already on file. Nothing is merged until you say so."
      />

      <Card>
        {pairs.length === 0 ? (
          <EmptyState
            icon={<Copy className="size-5" aria-hidden />}
            title="No probable duplicates"
            description="Matching runs on mobile number, passport, Emirates ID and email."
          />
        ) : (
          <ul className="divide-y divide-line">
            {pairs.map((pair) => (
              <li key={pair.lead.id} className="flex flex-wrap items-center gap-4 px-5 py-4">
                <div className="min-w-0 flex-1">
                  <Link href={pair.lead.url} className="text-h3 text-ink hover:text-accent-ink">
                    {pair.lead.name}
                  </Link>
                  <p className="text-small text-ink-faint">
                    {pair.lead.reference} · {pair.lead.mobile ?? pair.lead.email ?? 'no contact details'}
                  </p>
                </div>

                <StatusPill tone={pair.score >= 90 ? 'danger' : 'warning'}>
                  <Num value={pair.score} />% · {pair.reason}
                </StatusPill>

                <div className="min-w-0 flex-1">
                  <Link href={pair.match.url} className="text-h3 text-ink hover:text-accent-ink">
                    {pair.match.name}
                  </Link>
                  <p className="text-small text-ink-faint">{pair.match.reference}</p>
                </div>

                <div className="flex gap-2">
                  <Button
                    size="sm"
                    variant="secondary"
                    onClick={() => router.post(`/leads/${pair.lead.id}/convert`, { client_id: pair.match.id })}
                  >
                    Same person
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => router.post(`/leads/${pair.lead.id}/qualify`, { outcome: 'qualified' })}
                  >
                    Not a duplicate
                  </Button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
