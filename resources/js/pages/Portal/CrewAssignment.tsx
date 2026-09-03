import { PortalShell } from '@/components/portal/PortalShell'
import { Card, CardBody, DateText } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface Assignment {
  crew: string | null
  role: string | null
  yacht: string | null
  reference: string | null
  starts_at: string
  ends_at: string
  status: string
  dispatched: boolean
}

/**
 * A crew member's dispatch sheet, opened one-handed on a phone at the marina
 * gate. The two facts that matter — which yacht, what time — are the two
 * largest things on the page.
 */
export default function CrewAssignmentPortal({
  assignment,
  expires_at,
}: {
  assignment: Assignment
  expires_at: string | null
}) {
  return (
    <PortalShell title={assignment.yacht ?? 'Your assignment'} eyebrow={assignment.reference} expiresAt={expires_at}>
      <Card>
        <CardBody>
          <div className="flex flex-col gap-1">
            <span className="text-micro uppercase tracking-wide text-ink-faint">Report at</span>
            <span className="text-h1 text-ink">
              <DateText value={assignment.starts_at} withTime />
            </span>
            <span className="text-small text-ink-soft">
              Until <DateText value={assignment.ends_at} withTime />
            </span>
          </div>
        </CardBody>
      </Card>

      <Card>
        <CardBody>
          <dl className="grid grid-cols-2 gap-4">
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">You</dt>
              <dd className="text-body text-ink">{assignment.crew}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Role</dt>
              <dd className="text-body text-ink">{assignment.role ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Yacht</dt>
              <dd className="text-body text-ink">{assignment.yacht ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Status</dt>
              <dd>
                <StatusPill tone={assignment.dispatched ? 'success' : 'warning'}>
                  {assignment.dispatched ? 'Confirmed' : 'Not yet dispatched'}
                </StatusPill>
              </dd>
            </div>
          </dl>
        </CardBody>
      </Card>

      {!assignment.dispatched && (
        <p className="rounded-card border border-warning bg-warning-bg px-4 py-3 text-small text-warning">
          This assignment is not confirmed yet. Wait for dispatch before travelling.
        </p>
      )}
    </PortalShell>
  )
}
