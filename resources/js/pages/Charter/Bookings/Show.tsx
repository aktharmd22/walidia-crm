import { Link } from '@inertiajs/react'
import { CalendarCheck, FileSignature, Ship, Users } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { GateButton, GateCleared } from '@/components/gates/GateButton'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Button } from '@/ui/Button'
import type { GateResult, StatusTone } from '@/types'

interface BookingRecord {
  id: number
  reference: string
  status: string
  status_label: string
  status_tone: StatusTone
  starts_at: string
  ends_at: string
  starts_local: string
  timezone: string
  duration_hours: number
  guests_adults: number
  guests_children: number
  guest_count: number
  itinerary: string | null
  special_requests: string | null
  currency: string
  contract_signed_at: string | null
  operational_release_at: string | null
  is_released: boolean
  cancelled_at: string | null
  cancellation_reason: string | null
  client?: { id: number; name: string; kyc_status: string } | null
  yacht?: { id: number; name: string } | null
  marina?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  value?: string | null
}

interface ScheduleItem {
  id: number
  label: string
  amount: string
  due_at: string | null
  status: string
  cleared: number
  overdue: boolean
}

/**
 * The booking screen, built around the transitions the gate engine guards:
 * contract → deposit → Operational Release → confirmed.
 */
export default function BookingShow({
  record,
  timeline = [],
  gates,
  schedule = [],
  can,
}: {
  record: BookingRecord
  timeline?: TimelineEntry[]
  gates: { release: GateResult; contract: GateResult; board: GateResult }
  schedule?: ScheduleItem[]
  can: { update?: boolean; delete?: boolean; release?: boolean; confirm?: boolean; cancel?: boolean; override?: boolean }
}) {
  return (
    <DetailShell
      title={`${record.yacht?.name ?? 'Charter'} — ${record.reference}`}
      subtitle={record.client?.name}
      status={record.status_label}
      statusTone={record.status_tone}
      editUrl={can.update ? `/charter/bookings/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/charter/bookings/${record.id}` : undefined}
      backUrl="/charter/bookings"
      actions={[
        <Link key="costsheet" href={`/charter/bookings/${record.id}/cost-sheet`} method="post" as="button">
          <Button variant="secondary">Cost sheet</Button>
        </Link>,
      ]}
      facts={[
        { label: 'Reference', value: <span className="numeric">{record.reference}</span> },
        { label: 'Departs', value: <span className="numeric">{record.starts_local}</span> },
        { label: 'Timezone', value: <span className="numeric">{record.timezone}</span> },
        { label: 'Duration', value: <span className="numeric">{record.duration_hours} h</span> },
        { label: 'Guests', value: <Num value={record.guest_count} /> },
        { label: 'Marina', value: record.marina?.name ?? '—' },
        { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
        {
          label: 'Contract signed',
          value: record.contract_signed_at ? <DateText value={record.contract_signed_at} /> : 'Not yet',
        },
        {
          label: 'Value',
          value: record.value ? <Money amount={record.value} currency={record.currency} /> : '—',
        },
      ]}
      timeline={timeline}
      aside={
        <Card>
          <CardHeader>
            <CardTitle>Payment plan</CardTitle>
          </CardHeader>
          {schedule.length === 0 ? (
            <EmptyState title="No schedule" description="A payment plan is created when a proposal is accepted." />
          ) : (
            <ul className="divide-y divide-line">
              {schedule.map((item) => (
                <li key={item.id} className="flex items-center justify-between gap-3 px-5 py-3">
                  <span className="min-w-0">
                    <span className="block text-h3 capitalize text-ink">{item.label}</span>
                    <span className="block text-small text-ink-faint">
                      due <DateText value={item.due_at} />
                    </span>
                  </span>
                  <span className="text-end">
                    <Money amount={item.amount} currency={record.currency} className="block" />
                    <StatusPill tone={item.status === 'paid' ? 'success' : item.overdue ? 'danger' : 'warning'}>
                      {item.status === 'paid' ? 'Paid' : item.overdue ? 'Overdue' : 'Due'}
                    </StatusPill>
                  </span>
                </li>
              ))}
            </ul>
          )}
        </Card>
      }
    >
      {/* The guarded steps, in the order they actually happen. */}
      <Card>
        <CardHeader>
          <CardTitle>Progress</CardTitle>
          <span className="text-small text-ink-faint">Each step unlocks the next</span>
        </CardHeader>
        <CardBody className="flex flex-col gap-5">
          <div className="flex flex-col gap-2">
            <p className="flex items-center gap-2 text-h3 text-ink">
              <FileSignature className="size-4 text-ink-faint" aria-hidden />
              Charter agreement
            </p>
            {record.contract_signed_at ? (
              <GateCleared label={`Signed on ${new Date(record.contract_signed_at).toLocaleDateString()}`} />
            ) : (
              <GateButton
                gate={gates.contract}
                action={`/charter/bookings/${record.id}/contract`}
                label="Generate agreement"
                canOverride={can.override}
              />
            )}
          </div>

          <div className="flex flex-col gap-2">
            <p className="flex items-center gap-2 text-h3 text-ink">
              <CalendarCheck className="size-4 text-ink-faint" aria-hidden />
              Operational Release
            </p>
            {record.is_released ? (
              <GateCleared label="Released — crew and vendors can be dispatched." />
            ) : (
              <GateButton
                gate={gates.release}
                action={`/charter/bookings/${record.id}/release-operations`}
                label="Grant Operational Release"
                canOverride={can.override}
                {...(can.release ? {} : { disabled: true })}
              />
            )}
          </div>

          <div className="flex flex-col gap-2">
            <p className="flex items-center gap-2 text-h3 text-ink">
              <Users className="size-4 text-ink-faint" aria-hidden />
              Boarding
            </p>
            <GateButton
              gate={gates.board}
              action={`/charter/bookings/${record.id}/board`}
              label="Board guests"
              variant="secondary"
              canOverride={can.override}
            />
          </div>
        </CardBody>
      </Card>

      {record.itinerary ? (
        <Card>
          <CardHeader>
            <CardTitle>Itinerary</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.itinerary}</p>
          </CardBody>
        </Card>
      ) : (
        <Card>
          <EmptyState
            icon={<Ship className="size-5" aria-hidden />}
            title="No itinerary yet"
            description="Confirming without one is allowed, but it raises a task for Operations."
          />
        </Card>
      )}

      {record.special_requests && (
        <Card>
          <CardHeader>
            <CardTitle>Special requests</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.special_requests}</p>
          </CardBody>
        </Card>
      )}
    </DetailShell>
  )
}
