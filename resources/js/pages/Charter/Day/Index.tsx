import { Head, Link } from '@inertiajs/react'
import { Ship } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Card, EmptyState, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface Booking {
  id: number
  reference: string
  status_label: string
  status_tone: StatusTone
  starts_local: string
  guest_count: number
  is_released: boolean
  client?: { id: number; name: string } | null
  yacht?: { id: number; name: string } | null
  marina?: { id: number; name: string } | null
}

/**
 * What is on the water today. Opened on a phone at the marina, so the rows are
 * large and the only thing they do is take you into the charter.
 */
export default function CharterDayIndex({ bookings = [] }: { bookings?: Booking[] }) {
  return (
    <>
      <Head title="Charter Day" />

      <PageHeader title="Charter Day" description="Today and the next two days. Tap a charter to run it." />

      {bookings.length === 0 ? (
        <Card>
          <EmptyState
            icon={<Ship className="size-5" aria-hidden />}
            title="Nothing on the water"
            description="Confirmed charters departing in the next two days appear here."
          />
        </Card>
      ) : (
        <div className="flex flex-col gap-3">
          {bookings.map((booking) => (
            <Link
              key={booking.id}
              href={`/charter/day/${booking.id}`}
              className="rounded-card border border-line bg-hull p-5 transition-colors duration-fast hover:border-line-strong active:bg-deck"
            >
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0">
                  <p className="text-h2 text-ink">{booking.yacht?.name}</p>
                  <p className="text-body text-ink-soft">{booking.client?.name}</p>
                  <p className="numeric mt-1 text-body text-ink">
                    {booking.starts_local} · {booking.marina?.name ?? 'marina TBC'}
                  </p>
                </div>
                <div className="flex flex-col items-end gap-2">
                  <StatusPill tone={booking.status_tone}>{booking.status_label}</StatusPill>
                  <span className="text-small text-ink-faint">
                    <Num value={booking.guest_count} /> guests
                  </span>
                  {!booking.is_released && <StatusPill tone="warning">Not released</StatusPill>}
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </>
  )
}
