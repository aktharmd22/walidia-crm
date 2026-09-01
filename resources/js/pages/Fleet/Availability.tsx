import { Head, router } from '@inertiajs/react'
import { addDays, differenceInCalendarDays, format, parseISO } from 'date-fns'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, EmptyState } from '@/ui/Primitives'
import { Tooltip } from '@/ui/Overlays'
import { cn } from '@/lib/cn'
import type { StatusTone } from '@/types'

interface Block {
  id: number
  type: string
  tone: StatusTone
  starts_at: string
  ends_at: string
  expires_at: string | null
  note: string | null
}

interface Row {
  id: number
  name: string
  reference: string | null
  blocks: Block[]
}

const toneBar: Record<StatusTone, string> = {
  success: 'bg-success',
  info: 'bg-info',
  warning: 'bg-warning',
  attention: 'bg-attention',
  danger: 'bg-danger',
  neutral: 'bg-neutral',
}

/**
 * The fleet calendar. Bookings, option holds, maintenance and owner use all
 * come from one table, so what you see here is what the availability gate sees.
 */
export default function FleetAvailability({
  yachts = [],
  range,
}: {
  yachts?: Row[]
  range: { from: string; to: string }
}) {
  const from = parseISO(range.from)
  const to = parseISO(range.to)
  const days = Math.max(differenceInCalendarDays(to, from) + 1, 1)

  function shift(deltaDays: number) {
    router.get(
      '/fleet/availability',
      {
        from: format(addDays(from, deltaDays), 'yyyy-MM-dd'),
        to: format(addDays(to, deltaDays), 'yyyy-MM-dd'),
      },
      { preserveState: true },
    )
  }

  return (
    <>
      <Head title="Fleet availability" />

      <PageHeader
        title="Fleet availability"
        description={`${format(from, 'd MMM yyyy')} to ${format(to, 'd MMM yyyy')} · one row per yacht`}
        actions={
          <div className="flex gap-2">
            <Button variant="secondary" size="sm" icon={<ChevronLeft className="size-4" />} onClick={() => shift(-days)}>
              Earlier
            </Button>
            <Button variant="secondary" size="sm" iconEnd={<ChevronRight className="size-4" />} onClick={() => shift(days)}>
              Later
            </Button>
          </div>
        }
      />

      <div className="flex flex-wrap gap-4 text-small text-ink-soft">
        {[
          ['info', 'Booking'],
          ['warning', 'Option hold'],
          ['attention', 'Maintenance'],
          ['neutral', 'Owner use'],
        ].map(([tone, label]) => (
          <span key={label} className="flex items-center gap-2">
            <span className={cn('h-2 w-6 rounded-pill', toneBar[tone as StatusTone])} aria-hidden />
            {label}
          </span>
        ))}
      </div>

      <Card className="overflow-hidden">
        {yachts.length === 0 ? (
          <EmptyState title="No active yachts" description="Yachts marked active appear on this calendar." />
        ) : (
          <div className="overflow-x-auto">
            <div className="min-w-[900px]">
              {yachts.map((yacht) => (
                <div key={yacht.id} className="flex items-center gap-4 border-b border-line px-5 py-3 last:border-0">
                  <div className="w-48 shrink-0">
                    <p className="truncate text-h3 text-ink">{yacht.name}</p>
                    <p className="numeric text-small text-ink-faint">{yacht.reference}</p>
                  </div>

                  <div className="relative h-8 flex-1 rounded-pill bg-deck">
                    {yacht.blocks.map((block) => {
                      const start = Math.max(differenceInCalendarDays(parseISO(block.starts_at), from), 0)
                      const end = Math.min(differenceInCalendarDays(parseISO(block.ends_at), from) + 1, days)
                      const width = Math.max(end - start, 0.5)

                      return (
                        <Tooltip
                          key={block.id}
                          content={
                            <span>
                              {block.type.replace('_', ' ')} · {format(parseISO(block.starts_at), 'd MMM')} –{' '}
                              {format(parseISO(block.ends_at), 'd MMM')}
                              {block.note ? ` · ${block.note}` : ''}
                            </span>
                          }
                        >
                          <div
                            className={cn('absolute top-1 h-6 rounded-pill', toneBar[block.tone])}
                            style={{
                              insetInlineStart: `${(start / days) * 100}%`,
                              width: `${(width / days) * 100}%`,
                            }}
                          />
                        </Tooltip>
                      )
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </Card>
    </>
  )
}
