import { useState, type ReactNode } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import { Archive, MoreHorizontal, Pencil } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { cn } from '@/lib/cn'
import { Button } from '@/ui/Button'
import { Card, CardBody, DateText, EmptyState } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { DropdownMenu, Modal, type MenuItem } from '@/ui/Overlays'
import type { StatusTone } from '@/types'

export interface TimelineEntry {
  id: number
  type: string
  summary: string
  body?: string | null
  user?: string | null
  occurred_at: string | null
}

export interface DetailFact {
  label: string
  value: ReactNode
}

/** The spine's dots, one per tone. */
const toneDot: Record<StatusTone, string> = {
  success: 'bg-success',
  info: 'bg-info',
  warning: 'bg-warning',
  attention: 'bg-attention',
  danger: 'bg-danger',
  neutral: 'bg-line-strong',
}

const typeTone: Record<string, StatusTone> = {
  call: 'info',
  whatsapp: 'success',
  email: 'info',
  meeting: 'attention',
  note: 'neutral',
  status_change: 'warning',
  system: 'neutral',
  gate: 'danger',
}

/**
 * The record screen: identity and status at the top, key facts down the side,
 * the timeline in the middle. Split-pane on desktop, stacked on mobile.
 */
export function DetailShell({
  title,
  subtitle,
  status,
  statusTone = 'neutral',
  facts = [],
  timeline = [],
  editUrl,
  archiveUrl,
  backUrl,
  actions = [],
  menu = [],
  children,
  aside,
}: {
  title: string
  subtitle?: string | null
  status?: string | null
  statusTone?: StatusTone
  facts?: DetailFact[]
  timeline?: TimelineEntry[]
  editUrl?: string
  archiveUrl?: string
  backUrl?: string
  actions?: ReactNode[]
  menu?: MenuItem[]
  children?: ReactNode
  aside?: ReactNode
}) {
  const [confirmArchive, setConfirmArchive] = useState(false)

  return (
    <>
      <Head title={title} />

      <PageHeader
        title={title}
        description={subtitle ?? undefined}
        actions={
          <>
            {status && <StatusPill tone={statusTone}>{status}</StatusPill>}
            {actions}
            {editUrl && (
              <Link href={editUrl}>
                <Button variant="secondary" icon={<Pencil className="size-4" />}>
                  Edit
                </Button>
              </Link>
            )}
            {(menu.length > 0 || archiveUrl) && (
              <DropdownMenu
                label="MENU"
                items={[
                  ...menu,
                  ...(archiveUrl
                    ? [
                        {
                          key: 'archive',
                          label: 'Archive',
                          icon: <Archive className="size-4" />,
                          destructive: true,
                          separatorBefore: menu.length > 0,
                          onSelect: () => setConfirmArchive(true),
                        } satisfies MenuItem,
                      ]
                    : []),
                ]}
                trigger={
                  <Button variant="ghost" aria-label="More actions">
                    <MoreHorizontal className="size-4" />
                  </Button>
                }
              />
            )}
          </>
        }
      />

      {/*
       * The facts read across the top rather than down a column. A record's
       * key figures are what you came for; putting them in a right-hand rail
       * pushes them past the fold on a laptop and leaves the main column
       * carrying one paragraph and a lot of white.
       */}
      {facts.length > 0 && (
        <Card>
          <CardBody className="grid gap-x-6 gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {facts.map((fact) => (
              <div key={fact.label} className="min-w-0">
                <dt className="text-micro uppercase tracking-[0.06em] text-ink-faint">{fact.label}</dt>
                <dd className="mt-1 truncate text-body text-ink">{fact.value ?? '—'}</dd>
              </div>
            ))}
          </CardBody>
        </Card>
      )}

      <div className={cn('grid gap-5', aside ? 'xl:grid-cols-[1fr_340px]' : undefined)}>
        <div className="flex min-w-0 flex-col gap-5">
          {children}

          <Card>
            <CardBody className="p-6">
              <div className="mb-5 flex items-center justify-between gap-4">
                <h2 className="text-h2 text-ink">Timeline</h2>
                {timeline.length > 0 && (
                  <span className="numeric text-small text-ink-faint">{timeline.length} entries</span>
                )}
              </div>

              {timeline.length === 0 ? (
                <EmptyState
                  title="Nothing logged yet"
                  description="Calls, messages, status changes and gate decisions all appear here."
                />
              ) : (
                /* A thread with a spine: the eye follows the line down and the
                   dots mark where something actually happened. */
                <ol className="relative flex flex-col gap-6 ps-6 before:absolute before:inset-y-1 before:start-[5px] before:w-px before:bg-line">
                  {timeline.map((entry) => (
                    <li key={entry.id} className="relative min-w-0">
                      <span
                        className={cn(
                          'absolute start-[-24px] top-[5px] size-[11px] rounded-full ring-4 ring-hull',
                          toneDot[typeTone[entry.type] ?? 'neutral'],
                        )}
                        aria-hidden
                      />
                      <p className="text-h3 text-ink">{entry.summary}</p>
                      {entry.body && <p className="mt-1 text-body text-ink-soft">{entry.body}</p>}
                      <p className="mt-1 text-small text-ink-faint">
                        {entry.type.replace(/_/g, ' ')} · {entry.user ?? 'System'} ·{' '}
                        <DateText value={entry.occurred_at} withTime />
                      </p>
                    </li>
                  ))}
                </ol>
              )}
            </CardBody>
          </Card>
        </div>

        {aside && <div className="flex flex-col gap-5">{aside}</div>}
      </div>

      {archiveUrl && (
        <Modal
          open={confirmArchive}
          onOpenChange={setConfirmArchive}
          title={`Archive ${title}?`}
          description="It moves to the archive and can be restored. Nothing is deleted."
          footer={
            <>
              <Button variant="secondary" onClick={() => setConfirmArchive(false)}>
                Cancel
              </Button>
              <Button
                variant="destructive"
                onClick={() =>
                  router.delete(archiveUrl, {
                    onSuccess: () => {
                      setConfirmArchive(false)
                      if (backUrl) router.visit(backUrl)
                    },
                  })
                }
              >
                Archive
              </Button>
            </>
          }
        >
          <p className="text-body text-ink-soft">
            Everything attached to this record — documents, timeline, tasks — stays linked to it.
          </p>
        </Modal>
      )}
    </>
  )
}
