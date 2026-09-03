import { Head, router } from '@inertiajs/react'
import {
  addDays,
  addMonths,
  endOfMonth,
  format,
  isSameDay,
  isSameMonth,
  parse,
  startOfMonth,
  startOfWeek,
} from 'date-fns'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card } from '@/ui/Primitives'
import { cn } from '@/lib/cn'
import type { StatusTone } from '@/types'

interface CalendarEvent {
  id: string
  date: string
  label: string
  tone: StatusTone
  href: string
}

const toneDot: Record<StatusTone, string> = {
  success: 'bg-success',
  info: 'bg-info',
  warning: 'bg-warning',
  attention: 'bg-attention',
  danger: 'bg-danger',
  neutral: 'bg-neutral',
}

/**
 * One calendar for everything dated: charters, viewings, maintenance windows,
 * option holds and payment due dates. The month is server-driven, so the URL
 * is shareable and the query is paginated by month rather than unbounded.
 */
export default function Calendar({ events = [], month }: { events?: CalendarEvent[]; month: string }) {
  const current = parse(month, 'yyyy-MM', new Date())
  const gridStart = startOfWeek(startOfMonth(current), { weekStartsOn: 1 })
  const days = Array.from({ length: 42 }, (_, index) => addDays(gridStart, index))

  function go(delta: number) {
    router.get(
      '/dashboard/calendar',
      { month: format(addMonths(current, delta), 'yyyy-MM') },
      { preserveState: true },
    )
  }

  return (
    <>
      <Head title="Calendar" />

      <PageHeader
        title="Calendar"
        description={`${format(startOfMonth(current), 'd MMM')} to ${format(endOfMonth(current), 'd MMM yyyy')} · Asia/Dubai`}
        actions={
          <div className="flex items-center gap-2">
            <Button variant="secondary" size="sm" icon={<ChevronLeft className="size-4" />} onClick={() => go(-1)}>
              Previous
            </Button>
            <Button variant="secondary" size="sm" iconEnd={<ChevronRight className="size-4" />} onClick={() => go(1)}>
              Next
            </Button>
          </div>
        }
      />

      <Card className="overflow-hidden">
        <div className="grid grid-cols-7 border-b border-line bg-deck">
          {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((day) => (
            <div key={day} className="px-3 py-2 text-micro text-ink-faint">
              {day}
            </div>
          ))}
        </div>

        <div className="grid grid-cols-7">
          {days.map((day) => {
            const dayEvents = events.filter((event) => isSameDay(new Date(event.date), day))

            return (
              <div
                key={day.toISOString()}
                className={cn(
                  'min-h-[104px] border-b border-e border-line p-2',
                  !isSameMonth(day, current) && 'bg-deck',
                )}
              >
                <span
                  className={cn(
                    'numeric text-small',
                    isSameDay(day, new Date()) ? 'font-medium text-accent-ink' : 'text-ink-faint',
                  )}
                >
                  {format(day, 'd')}
                </span>

                <ul className="mt-1 flex flex-col gap-1">
                  {dayEvents.map((event) => (
                    <li key={event.id}>
                      <a
                        href={event.href}
                        className="flex items-center gap-2 rounded-pill px-1 py-px text-micro text-ink hover:bg-deck"
                      >
                        <span className={cn('size-[6px] shrink-0 rounded-full', toneDot[event.tone])} aria-hidden />
                        <span className="truncate">{event.label}</span>
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            )
          })}
        </div>
      </Card>
    </>
  )
}
