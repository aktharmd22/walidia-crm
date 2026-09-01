import { useState } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import { GripVertical, Plus } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Modal, Tabs } from '@/ui/Overlays'
import { Select, Textarea } from '@/ui/Field'
import { DateText, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { cn } from '@/lib/cn'
import type { StatusTone } from '@/types'

interface Card {
  id: number
  reference: string | null
  title: string
  value?: string | null
  currency: string
  days_in_stage: number
  expected_close_date: string | null
  client?: { id: number; name: string } | null
  assignee?: { id: number; name: string } | null
  yacht?: { id: number; name: string } | null
  url: string
}

interface Column {
  id: number
  key: string
  name: string
  tone: StatusTone
  probability: number
  is_won: boolean
  is_lost: boolean
  cards: Card[]
  total: number
}

/**
 * The pipeline board.
 *
 * Dragging a card is a stage transition, and stage transitions are what the
 * gate engine guards. A blocked move returns the reason and the card stays
 * where it was — never a silent failure.
 */
export default function DealBoard({
  pipeline,
  pipelines = [],
  columns = [],
  filters,
  lostReasons = [],
}: {
  pipeline: { id: number; key: string; name: string }
  pipelines?: { id: number; key: string; name: string }[]
  columns?: Column[]
  filters: { mine?: boolean }
  lostReasons?: { id: number; label: string }[]
}) {
  const [dragging, setDragging] = useState<number | null>(null)
  const [lostFor, setLostFor] = useState<{ dealId: number; stageId: number } | null>(null)
  const [lostReason, setLostReason] = useState('')
  const [lostNotes, setLostNotes] = useState('')

  function move(dealId: number, stageId: number, column: Column) {
    if (column.is_lost) {
      setLostFor({ dealId, stageId })
      return
    }

    router.post(`/deals/${dealId}/stage`, { stage_id: stageId }, { preserveScroll: true })
  }

  return (
    <>
      <Head title={`${pipeline.name} pipeline`} />

      <PageHeader
        title={`${pipeline.name} pipeline`}
        description="Drag a card to move the deal. Moves that fail a gate are refused with the reason."
        actions={
          <Link href="/deals/create">
            <Button variant="primary" icon={<Plus className="size-4" />}>
              New deal
            </Button>
          </Link>
        }
      />

      <div className="flex flex-wrap items-center gap-3">
        <Tabs
          value={pipeline.key}
          onValueChange={(key) => router.get('/deals/board', { pipeline: key, mine: filters.mine })}
          items={pipelines.map((item) => ({ value: item.key, label: item.name }))}
        />
        <Button
          variant={filters.mine ? 'primary' : 'secondary'}
          size="sm"
          onClick={() => router.get('/deals/board', { pipeline: pipeline.key, mine: !filters.mine })}
        >
          {filters.mine ? 'Showing mine' : 'Show only mine'}
        </Button>
      </div>

      <div className="overflow-x-auto pb-2">
        <div className="flex min-w-max gap-4">
          {columns.map((column) => (
            <section
              key={column.id}
              onDragOver={(event) => event.preventDefault()}
              onDrop={() => {
                if (dragging !== null) move(dragging, column.id, column)
                setDragging(null)
              }}
              className="flex w-[280px] shrink-0 flex-col rounded-card border border-line bg-hull"
            >
              <header className="flex items-center justify-between gap-2 border-b border-line px-4 py-3">
                <span className="flex items-center gap-2">
                  <StatusPill tone={column.tone}>{column.name}</StatusPill>
                  <Num value={column.cards.length} className="text-small text-ink-faint" />
                </span>
                <Money amount={column.total} compact className="text-small text-ink-soft" />
              </header>

              <div className="flex flex-col gap-2 p-2">
                {column.cards.length === 0 && (
                  <p className="px-2 py-6 text-center text-small text-ink-faint">Nothing here</p>
                )}

                {column.cards.map((card) => (
                  <article
                    key={card.id}
                    draggable
                    onDragStart={() => setDragging(card.id)}
                    onDragEnd={() => setDragging(null)}
                    className={cn(
                      'group rounded-card border border-line bg-hull p-3 transition-colors duration-fast',
                      'hover:border-line-strong',
                      dragging === card.id && 'opacity-50',
                    )}
                  >
                    <div className="flex items-start gap-2">
                      <GripVertical className="mt-px size-4 shrink-0 cursor-grab text-ink-faint opacity-0 group-hover:opacity-100" aria-hidden />
                      <div className="min-w-0 flex-1">
                        <Link href={card.url} className="block truncate text-h3 text-ink hover:text-accent">
                          {card.title}
                        </Link>
                        {card.client && <p className="truncate text-small text-ink-faint">{card.client.name}</p>}

                        <div className="mt-2 flex items-center justify-between gap-2">
                          {card.value !== undefined && card.value !== null ? (
                            <Money amount={card.value} currency={card.currency} compact className="text-body text-ink" />
                          ) : (
                            <span className="text-small text-ink-faint">Value restricted</span>
                          )}
                          <span className="numeric text-micro text-ink-faint">{card.days_in_stage}d</span>
                        </div>

                        {card.expected_close_date && (
                          <p className="mt-1 text-small text-ink-faint">
                            Close <DateText value={card.expected_close_date} />
                          </p>
                        )}
                      </div>
                    </div>
                  </article>
                ))}
              </div>
            </section>
          ))}
        </div>
      </div>

      <Modal
        open={lostFor !== null}
        onOpenChange={(open) => !open && setLostFor(null)}
        title="Why was this lost?"
        description="A lost deal without a reason cannot be reported on, so the reason is required."
        footer={
          <>
            <Button variant="secondary" onClick={() => setLostFor(null)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              disabled={!lostReason}
              onClick={() => {
                if (!lostFor) return
                router.post(
                  `/deals/${lostFor.dealId}/stage`,
                  { stage_id: lostFor.stageId, lost_reason_id: lostReason, lost_notes: lostNotes },
                  {
                    preserveScroll: true,
                    onSuccess: () => {
                      setLostFor(null)
                      setLostReason('')
                      setLostNotes('')
                    },
                  },
                )
              }}
            >
              Mark as lost
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Reason"
            required
            placeholder="Choose a reason…"
            value={lostReason}
            onChange={(event) => setLostReason(event.target.value)}
            options={lostReasons.map((reason) => ({ value: reason.id, label: reason.label }))}
          />
          <Textarea label="Notes" value={lostNotes} onChange={(event) => setLostNotes(event.target.value)} />
        </div>
      </Modal>
    </>
  )
}
