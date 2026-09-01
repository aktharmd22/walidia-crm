import { Head, Link, router } from '@inertiajs/react'
import { ShieldAlert } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface Row {
  id: number
  yacht: string | null
  yacht_id: number
  name: string
  type: string
  expires_on: string | null
  is_expired: boolean
  blocks_charter: boolean
  tone: StatusTone
  url: string
}

/**
 * Fleet compliance at a glance. A certificate marked "blocks charter" that has
 * lapsed is not a reminder — it is a charter that cannot sail.
 */
export default function CertificateExpiry({ rows = [], days }: { rows?: Row[]; days: number }) {
  return (
    <>
      <Head title="Certificate expiry" />

      <PageHeader
        title="Certificate expiry"
        description={`Everything expiring within ${days} days, and everything already expired.`}
        actions={
          <div className="flex gap-2">
            {[30, 90, 180].map((window) => (
              <Button
                key={window}
                size="sm"
                variant={days === window ? 'primary' : 'secondary'}
                onClick={() => router.get('/management/certificates/expiry', { days: window })}
              >
                {window} days
              </Button>
            ))}
          </div>
        }
      />

      <Card>
        {rows.length === 0 ? (
          <EmptyState
            icon={<ShieldAlert className="size-5" aria-hidden />}
            title="Nothing expiring"
            description="Every certificate on file is valid beyond this window."
          />
        ) : (
          <ul className="divide-y divide-line">
            {rows.map((row) => (
              <li key={row.id} className="flex flex-wrap items-center gap-3 px-5 py-3">
                <Link href={row.url} className="min-w-0 flex-1">
                  <span className="block text-h3 text-ink">{row.name}</span>
                  <span className="block text-small text-ink-faint">
                    {row.yacht} · {row.type.replace(/_/g, ' ')}
                  </span>
                </Link>
                <DateText value={row.expires_on} className="text-small text-ink-soft" />
                {row.blocks_charter && <StatusPill tone="danger">Blocks charter</StatusPill>}
                <StatusPill tone={row.tone}>{row.is_expired ? 'Expired' : 'Expiring'}</StatusPill>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
