import { Head, Link } from '@inertiajs/react'
import { AlertTriangle, CalendarClock, ShieldAlert } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Card, CardHeader, CardTitle, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface Blocker {
  id: string
  rule: string
  subject: string
  message: string
  resolution: { label: string; href: string } | null
  href: string
}

interface Expiring {
  id: string
  kind: string
  subject: string
  expires_on: string
  href: string
}

/**
 * Everything the gate engine is currently unhappy about, in one place: hard
 * blocks first, then soft warnings, then what is about to expire.
 */
export default function Alerts({
  hard = [],
  soft = [],
  expiring = [],
}: {
  hard?: Blocker[]
  soft?: Blocker[]
  expiring?: Expiring[]
}) {
  return (
    <>
      <Head title="Alerts & Blockers" />

      <PageHeader
        title="Alerts & Blockers"
        description="A hard gate stops a transition until it is resolved. A soft gate lets the work proceed and asks someone to look."
      />

      <Card>
        <CardHeader>
          <CardTitle>Blocked</CardTitle>
          <StatusPill tone="danger">{hard.length} hard</StatusPill>
        </CardHeader>
        {hard.length === 0 ? (
          <EmptyState
            icon={<ShieldAlert className="size-5" aria-hidden />}
            title="Nothing is blocked"
            description="When a transition fails a hard gate it appears here, with the exact condition that stopped it."
          />
        ) : (
          <ul className="divide-y divide-line">
            {hard.map((blocker) => (
              <li key={blocker.id} className="flex flex-wrap items-start gap-3 px-5 py-4">
                <StatusPill tone="danger">Blocked</StatusPill>
                <span className="min-w-0 flex-1">
                  <Link href={blocker.href} className="block text-h3 text-ink hover:text-accent">
                    {blocker.subject}
                  </Link>
                  <span className="block text-small text-ink-soft">{blocker.message}</span>
                </span>
                {blocker.resolution && (
                  <Link href={blocker.resolution.href} className="text-small text-accent hover:underline">
                    {blocker.resolution.label}
                  </Link>
                )}
              </li>
            ))}
          </ul>
        )}
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Warnings</CardTitle>
          <StatusPill tone="warning">{soft.length} soft</StatusPill>
        </CardHeader>
        {soft.length === 0 ? (
          <EmptyState
            icon={<AlertTriangle className="size-5" aria-hidden />}
            title="No warnings"
            description="Missing itineraries, unchecked weather, incomplete manifests and expiring documents surface here."
          />
        ) : (
          <ul className="divide-y divide-line">
            {soft.map((blocker) => (
              <li key={blocker.id} className="flex flex-wrap items-start gap-3 px-5 py-4">
                <StatusPill tone="warning">Warning</StatusPill>
                <span className="min-w-0 flex-1">
                  <Link href={blocker.href} className="block text-h3 text-ink hover:text-accent">
                    {blocker.subject}
                  </Link>
                  <span className="block text-small text-ink-soft">{blocker.message}</span>
                </span>
              </li>
            ))}
          </ul>
        )}
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Expiring soon</CardTitle>
        </CardHeader>
        {expiring.length === 0 ? (
          <EmptyState
            icon={<CalendarClock className="size-5" aria-hidden />}
            title="Nothing expiring in the next 30 days"
            description="Certificates, crew documents, listing agreements and option holds are scanned daily."
          />
        ) : (
          <ul className="divide-y divide-line">
            {expiring.map((item) => (
              <li key={item.id} className="flex items-center gap-3 px-5 py-3">
                <StatusPill tone="warning">{item.kind}</StatusPill>
                <Link href={item.href} className="min-w-0 flex-1 truncate text-h3 text-ink hover:text-accent">
                  {item.subject}
                </Link>
                <DateText value={item.expires_on} className="text-small text-ink-soft" />
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
