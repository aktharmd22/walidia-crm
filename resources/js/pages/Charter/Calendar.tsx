import { Head, Link, router } from '@inertiajs/react'
import { addMonths, format, parseISO } from 'date-fns'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, DateText, EmptyState, Money } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface Booking {
  id: number
  reference: string
  status_label: string
  status_tone: StatusTone
  starts_at: string
  starts_local: string
  is_released: boolean
  currency: string
  value?: string | null
  client?: { id: number; name: string } | null
  yacht?: { id: number; name: string } | null
  marina?: { id: number; name: string } | null
  url: string
}

/** Charters in date order, grouped by day. */
export default function CharterCalendar({
  bookings = [],
  range,
}: {
  bookings?: Booking[]
  range: { from: string; to: string }
}) {
  const grouped = bookings.reduce<Record<string, Booking[]>>((accumulator, booking) => {
    const day = booking.starts_local.slice(0, 10)
    accumulator[day] = [...(accumulator[day] ?? []), booking]
    return accumulator
  }, {})

  function shift(months: number) {
    router.get('/charter/calendar', {
      from: format(addMonths(parseISO(range.from), months), 'yyyy-MM-dd'),
      to: format(addMonths(parseISO(range.to), months), 'yyyy-MM-dd'),
    })
  }

  return (
    <>
      <Head title="Charter calendar" />

      <PageHeader
        title="Charter calendar"
        description={`${format(parseISO(range.from), 'd MMM yyyy')} to ${format(parseISO(range.to), 'd MMM yyyy')}`}
        actions={
          <div className="flex gap-2">
            <Button variant="secondary" size="sm" icon={<ChevronLeft className="size-4" />} onClick={() => shift(-1)}>
              Earlier
            </Button>
            <Button variant="secondary" size="sm" iconEnd={<ChevronRight className="size-4" />} onClick={() => shift(1)}>
              Later
            </Button>
          </div>
        }
      />

      {Object.keys(grouped).length === 0 ? (
        <Card>
          <EmptyState title="Nothing scheduled" description="Confirmed and pending charters appear here in date order." />
        </Card>
      ) : (
        <div className="flex flex-col gap-4">
          {Object.entries(grouped).map(([day, dayBookings]) => (
            <Card key={day}>
              <div className="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
                <span className="text-h3 text-ink">
                  <DateText value={day} />
                </span>
                <span className="text-small text-ink-faint">
                  {dayBookings.length} charter{dayBookings.length === 1 ? '' : 's'}
                </span>
              </div>
              <ul className="divide-y divide-line">
                {dayBookings.map((booking) => (
                  <li key={booking.id}>
                    <Link href={booking.url} className="flex flex-wrap items-center gap-3 px-5 py-3 hover:bg-deck">
                      <span className="numeric text-body text-ink">{booking.starts_local.slice(11, 16)}</span>
                      <span className="min-w-0 flex-1">
                        <span className="block truncate text-h3 text-ink">{booking.yacht?.name}</span>
                        <span className="block truncate text-small text-ink-faint">
                          {booking.client?.name} · {booking.marina?.name ?? 'marina TBC'}
                        </span>
                      </span>
                      <StatusPill tone={booking.status_tone}>{booking.status_label}</StatusPill>
                      {booking.value && <Money amount={booking.value} currency={booking.currency} />}
                    </Link>
                  </li>
                ))}
              </ul>
            </Card>
          ))}
        </div>
      )}
    </>
  )
}
