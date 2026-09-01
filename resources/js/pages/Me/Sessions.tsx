import { Head, router } from '@inertiajs/react'
import { Monitor } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface SessionRow {
  id: string
  ip_address: string | null
  user_agent: string | null
  last_active: string
  is_current: boolean
}

/** Reads the browser and platform out of a user-agent string, roughly. */
function describe(agent: string | null): string {
  if (!agent) return 'Unknown device'

  const browser =
    /Edg\//.test(agent) ? 'Edge'
    : /Chrome\//.test(agent) ? 'Chrome'
    : /Safari\//.test(agent) ? 'Safari'
    : /Firefox\//.test(agent) ? 'Firefox'
    : 'Browser'

  const platform =
    /Windows/.test(agent) ? 'Windows'
    : /iPhone|iPad/.test(agent) ? 'iOS'
    : /Android/.test(agent) ? 'Android'
    : /Mac OS X/.test(agent) ? 'macOS'
    : /Linux/.test(agent) ? 'Linux'
    : 'Unknown platform'

  return `${browser} on ${platform}`
}

export default function Sessions({ sessions = [] }: { sessions?: SessionRow[] }) {
  return (
    <>
      <Head title="Active sessions" />

      <PageHeader
        title="Active sessions"
        description="Every device signed in to your account. Sessions end after 8 hours of inactivity."
        actions={
          sessions.length > 1 ? (
            <Button variant="destructive" onClick={() => router.delete('/me/sessions')}>
              Sign out everywhere else
            </Button>
          ) : undefined
        }
      />

      <Card>
        {sessions.length === 0 ? (
          <EmptyState
            icon={<Monitor className="size-5" aria-hidden />}
            title="No other sessions"
            description="You are signed in on this device only."
          />
        ) : (
          <ul className="divide-y divide-line">
            {sessions.map((session) => (
              <li key={session.id} className="flex flex-wrap items-center gap-3 px-5 py-4">
                <span className="grid size-8 shrink-0 place-items-center rounded-card bg-deck text-ink-faint">
                  <Monitor className="size-4" aria-hidden />
                </span>

                <span className="min-w-0 flex-1">
                  <span className="flex items-center gap-2">
                    <span className="text-h3 text-ink">{describe(session.user_agent)}</span>
                    {session.is_current && <StatusPill tone="success">This device</StatusPill>}
                  </span>
                  <span className="block text-small text-ink-faint">
                    <span className="numeric">{session.ip_address ?? 'Unknown IP'}</span>
                    {' · last active '}
                    <DateText value={session.last_active} withTime />
                  </span>
                </span>

                {!session.is_current && (
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => router.delete(`/me/sessions/${session.id}`)}
                  >
                    Sign out
                  </Button>
                )}
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
