import { useState, type ReactNode } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import { Archive, MoreHorizontal, Pencil } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState } from '@/ui/Primitives'
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

      <div className="grid gap-5 xl:grid-cols-[1fr_340px]">
        <div className="flex flex-col gap-5">
          {children}

          <Card>
            <CardHeader>
              <CardTitle>Timeline</CardTitle>
              <span className="text-small text-ink-faint">{timeline.length} entries</span>
            </CardHeader>
            {timeline.length === 0 ? (
              <EmptyState
                title="Nothing logged yet"
                description="Calls, messages, status changes and gate decisions all appear here."
              />
            ) : (
              <ul className="divide-y divide-line">
                {timeline.map((entry) => (
                  <li key={entry.id} className="flex gap-3 px-5 py-4">
                    <StatusPill tone={typeTone[entry.type] ?? 'neutral'}>{entry.type.replace('_', ' ')}</StatusPill>
                    <div className="min-w-0 flex-1">
                      <p className="text-h3 text-ink">{entry.summary}</p>
                      {entry.body && <p className="mt-1 text-body text-ink-soft">{entry.body}</p>}
                      <p className="mt-1 text-small text-ink-faint">
                        {entry.user ?? 'System'} · <DateText value={entry.occurred_at} withTime />
                      </p>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </Card>
        </div>

        <div className="flex flex-col gap-5">
          {facts.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Details</CardTitle>
              </CardHeader>
              <CardBody>
                <dl className="flex flex-col gap-3">
                  {facts.map((fact) => (
                    <div key={fact.label} className="flex items-start justify-between gap-4">
                      <dt className="text-small text-ink-faint">{fact.label}</dt>
                      <dd className="text-body text-ink text-end">{fact.value ?? '—'}</dd>
                    </div>
                  ))}
                </dl>
              </CardBody>
            </Card>
          )}
          {aside}
        </div>
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
