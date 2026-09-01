import { useState } from 'react'
import { Head, router, useForm } from '@inertiajs/react'
import {
  Anchor,
  Check,
  ClipboardCheck,
  LifeBuoy,
  Plus,
  Ship,
  UserCheck,
} from 'lucide-react'
import { GatePanel } from '@/components/gates/GateButton'
import { Button } from '@/ui/Button'
import { Card, CardBody, DateText, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Drawer, Modal } from '@/ui/Overlays'
import { Input, Select, Textarea } from '@/ui/Field'
import { cn } from '@/lib/cn'
import type { GateResult, StatusTone } from '@/types'

interface Guest {
  id: number
  name: string
  nationality: string | null
  is_lead_guest: boolean
  id_verified: boolean
  checked_in_at: string | null
}

interface ChecklistEntry {
  id: number
  key: string
  title: string
  section: string | null
  status: string
  is_blocking: boolean
}

interface LogEntry {
  id: number
  type: string
  body: string | null
  occurred_at: string
  by: string | null
}

interface Extra {
  id: number
  description: string
  amount: string
  status: string
}

interface BookingRecord {
  id: number
  reference: string
  status: string
  status_label: string
  status_tone: StatusTone
  starts_local: string
  guest_count: number
  currency: string
  client?: { id: number; name: string } | null
  yacht?: { id: number; name: string } | null
  marina?: { id: number; name: string } | null
}

/**
 * Charter Day.
 *
 * Built for a phone on a dock: 44px targets, one action per tap, no nested
 * modals, and nothing that needs two hands. Desktop inherits the same layout
 * rather than the other way round.
 */
export default function CharterDay({
  record,
  gate,
  guests = [],
  checklist,
  log = [],
  extras = [],
  can,
}: {
  record: BookingRecord
  gate: GateResult
  guests?: Guest[]
  checklist?: { id: number; completion_pct: number; items: ChecklistEntry[] } | null
  log?: LogEntry[]
  extras?: Extra[]
  can: { board?: boolean; override?: boolean }
}) {
  const [extraOpen, setExtraOpen] = useState(false)
  const [incidentOpen, setIncidentOpen] = useState(false)
  const [noteOpen, setNoteOpen] = useState(false)

  const extra = useForm({ description: '', quantity: '1', unit_price: '' })
  const incident = useForm({ type: 'guest_injury', severity: 'minor', description: '', injuries: false })
  const note = useForm({ event_type: 'note', body: '', location: '' })

  const verified = guests.filter((guest) => guest.id_verified).length
  const boarded = record.status === 'in_progress' || record.status === 'completed'

  return (
    <>
      <Head title={`Charter day — ${record.yacht?.name ?? record.reference}`} />

      {/* Identity strip: what boat, whose charter, when. */}
      <div className="rounded-card border border-line bg-hull p-5">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="min-w-0">
            <h1 className="text-h1 text-ink">{record.yacht?.name ?? 'Charter'}</h1>
            <p className="text-body text-ink-soft">
              {record.client?.name} · {record.reference}
            </p>
            <p className="numeric mt-1 text-body text-ink">
              {record.starts_local} · {record.marina?.name ?? 'marina TBC'}
            </p>
          </div>
          <StatusPill tone={record.status_tone}>{record.status_label}</StatusPill>
        </div>
      </div>

      {/* The one decision this screen exists for. */}
      {!boarded && (
        <Card>
          <CardBody className="flex flex-col gap-3">
            <p className="flex items-center gap-2 text-h2 text-ink">
              <UserCheck className="size-5 text-ink-faint" aria-hidden />
              Boarding
            </p>
            <p className="text-body text-ink-soft">
              <Num value={verified} /> of <Num value={guests.length} /> guests verified
              {checklist && ` · checklist ${checklist.completion_pct}%`}
            </p>

            <GatePanel gate={gate} />

            <Button
              variant="primary"
              size="lg"
              block
              disabled={gate.verdict === 'block' || !can.board}
              icon={<Check className="size-5" />}
              onClick={() => router.post(`/charter/day/${record.id}/board`, {}, { preserveScroll: true })}
            >
              Board guests
            </Button>
          </CardBody>
        </Card>
      )}

      {/* Big, thumb-sized actions. */}
      <div className="grid grid-cols-2 gap-3">
        <ActionTile
          icon={<Anchor className="size-5" aria-hidden />}
          label="Departed"
          onClick={() =>
            router.post(`/charter/day/${record.id}/log`, { event_type: 'departure', body: 'Departed' }, { preserveScroll: true })
          }
        />
        <ActionTile
          icon={<Ship className="size-5" aria-hidden />}
          label="Returned"
          onClick={() =>
            router.post(`/charter/day/${record.id}/log`, { event_type: 'arrival', body: 'Returned to berth' }, { preserveScroll: true })
          }
        />
        <ActionTile icon={<Plus className="size-5" aria-hidden />} label="Guest request" onClick={() => setExtraOpen(true)} />
        <ActionTile
          icon={<LifeBuoy className="size-5" aria-hidden />}
          label="Incident"
          tone="danger"
          onClick={() => setIncidentOpen(true)}
        />
      </div>

      {/* Guests: one tap to verify. */}
      <Card>
        <div className="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
          <span className="text-h2 text-ink">Guests</span>
          <span className="text-small text-ink-faint">
            <Num value={verified} />/<Num value={guests.length} /> verified
          </span>
        </div>
        <ul className="divide-y divide-line">
          {guests.length === 0 && <li className="px-5 py-4 text-body text-ink-faint">No guests on the manifest yet.</li>}
          {guests.map((guest) => (
            <li key={guest.id} className="flex items-center gap-3 px-5 py-3">
              <span className="min-w-0 flex-1">
                <span className="block truncate text-h3 text-ink">
                  {guest.name}
                  {guest.is_lead_guest && <span className="ms-2 text-micro text-ink-faint">lead</span>}
                </span>
                <span className="block text-small text-ink-faint">{guest.nationality ?? '—'}</span>
              </span>
              {guest.id_verified ? (
                <StatusPill tone="success">Verified</StatusPill>
              ) : (
                <Button
                  size="lg"
                  variant="secondary"
                  onClick={() => router.post(`/charter/day/${record.id}/guests/${guest.id}/verify`, {}, { preserveScroll: true })}
                >
                  Verify ID
                </Button>
              )}
            </li>
          ))}
        </ul>
      </Card>

      {/* Checklist: tap to complete, blocking items marked. */}
      {checklist && (
        <Card>
          <div className="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
            <span className="flex items-center gap-2 text-h2 text-ink">
              <ClipboardCheck className="size-5 text-ink-faint" aria-hidden />
              Checklist
            </span>
            <span className="numeric text-small text-ink-soft">{checklist.completion_pct}%</span>
          </div>
          <ul className="divide-y divide-line">
            {checklist.items.map((item) => (
              <li key={item.id} className="flex items-center gap-3 px-5 py-3">
                <span className="min-w-0 flex-1">
                  <span className="block text-h3 text-ink">{item.title}</span>
                  {item.is_blocking && <span className="text-micro text-danger">Blocks boarding</span>}
                </span>
                {item.status === 'done' ? (
                  <StatusPill tone="success">Done</StatusPill>
                ) : (
                  <Button
                    size="lg"
                    variant="secondary"
                    icon={<Check className="size-4" />}
                    onClick={() =>
                      router.post(
                        `/charter/day/${record.id}/checklist/${item.id}/complete`,
                        {},
                        { preserveScroll: true },
                      )
                    }
                  >
                    Done
                  </Button>
                )}
              </li>
            ))}
          </ul>
        </Card>
      )}

      {/* Extras raised today. */}
      {extras.length > 0 && (
        <Card>
          <div className="border-b border-line px-5 py-3 text-h2 text-ink">Guest requests</div>
          <ul className="divide-y divide-line">
            {extras.map((item) => (
              <li key={item.id} className="flex items-center justify-between gap-3 px-5 py-3">
                <span className="min-w-0 truncate text-body text-ink">{item.description}</span>
                <span className="flex items-center gap-3">
                  <Money amount={item.amount} currency={record.currency} />
                  <StatusPill tone={item.status === 'approved' ? 'success' : 'warning'}>{item.status}</StatusPill>
                </span>
              </li>
            ))}
          </ul>
        </Card>
      )}

      {/* The day's log, newest first. */}
      <Card>
        <div className="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
          <span className="text-h2 text-ink">Log</span>
          <Button size="sm" variant="secondary" onClick={() => setNoteOpen(true)}>
            Add note
          </Button>
        </div>
        <ul className="divide-y divide-line">
          {log.length === 0 && <li className="px-5 py-4 text-body text-ink-faint">Nothing logged yet.</li>}
          {log.map((entry) => (
            <li key={entry.id} className="flex gap-3 px-5 py-3">
              <StatusPill tone={entry.type === 'incident' ? 'danger' : 'neutral'}>
                {entry.type.replace(/_/g, ' ')}
              </StatusPill>
              <span className="min-w-0 flex-1">
                <span className="block text-body text-ink">{entry.body}</span>
                <span className="block text-small text-ink-faint">
                  {entry.by ?? 'System'} · <DateText value={entry.occurred_at} withTime />
                </span>
              </span>
            </li>
          ))}
        </ul>
      </Card>

      <Drawer
        open={extraOpen}
        onOpenChange={setExtraOpen}
        title="Guest request"
        description="Recorded now, priced onto the cost sheet when it is approved."
        footer={
          <>
            <Button variant="secondary" onClick={() => setExtraOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={extra.processing}
              onClick={() =>
                extra.post(`/charter/day/${record.id}/extras`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    extra.reset()
                    setExtraOpen(false)
                  },
                })
              }
            >
              Record request
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input
            label="What did they ask for?"
            required
            value={extra.data.description}
            error={extra.errors.description}
            onChange={(event) => extra.setData('description', event.target.value)}
          />
          <Input
            label="Quantity"
            numeric
            required
            value={extra.data.quantity}
            onChange={(event) => extra.setData('quantity', event.target.value)}
          />
          <Input
            label="Price each"
            numeric
            required
            value={extra.data.unit_price}
            error={extra.errors.unit_price}
            onChange={(event) => extra.setData('unit_price', event.target.value)}
          />
        </div>
      </Drawer>

      <Modal
        open={incidentOpen}
        onOpenChange={setIncidentOpen}
        title="Report an incident"
        description="Recorded immediately, with your name and the time."
        footer={
          <>
            <Button variant="secondary" onClick={() => setIncidentOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              loading={incident.processing}
              onClick={() =>
                incident.post(`/charter/day/${record.id}/incidents`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    incident.reset()
                    setIncidentOpen(false)
                  },
                })
              }
            >
              Report it
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Type"
            required
            value={incident.data.type}
            onChange={(event) => incident.setData('type', event.target.value)}
            options={[
              { value: 'guest_injury', label: 'Guest injury' },
              { value: 'crew_injury', label: 'Crew injury' },
              { value: 'mechanical', label: 'Mechanical failure' },
              { value: 'grounding', label: 'Grounding' },
              { value: 'collision', label: 'Collision' },
              { value: 'weather', label: 'Weather' },
              { value: 'security', label: 'Security' },
              { value: 'other', label: 'Other' },
            ]}
          />
          <Select
            label="Severity"
            required
            value={incident.data.severity}
            onChange={(event) => incident.setData('severity', event.target.value)}
            options={[
              { value: 'minor', label: 'Minor' },
              { value: 'moderate', label: 'Moderate' },
              { value: 'major', label: 'Major' },
              { value: 'critical', label: 'Critical' },
            ]}
          />
          <Textarea
            label="What happened?"
            required
            rows={4}
            value={incident.data.description}
            error={incident.errors.description}
            onChange={(event) => incident.setData('description', event.target.value)}
          />
        </div>
      </Modal>

      <Modal
        open={noteOpen}
        onOpenChange={setNoteOpen}
        title="Add to the log"
        footer={
          <>
            <Button variant="secondary" onClick={() => setNoteOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={note.processing}
              onClick={() =>
                note.post(`/charter/day/${record.id}/log`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    note.reset()
                    setNoteOpen(false)
                  },
                })
              }
            >
              Log it
            </Button>
          </>
        }
      >
        <Textarea
          label="Note"
          required
          rows={3}
          value={note.data.body}
          error={note.errors.body}
          onChange={(event) => note.setData('body', event.target.value)}
        />
      </Modal>
    </>
  )
}

/** A thumb-sized action. Deliberately large: this is used standing up. */
function ActionTile({
  icon,
  label,
  onClick,
  tone = 'default',
}: {
  icon: React.ReactNode
  label: string
  onClick: () => void
  tone?: 'default' | 'danger'
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        'flex min-h-[88px] flex-col items-center justify-center gap-2 rounded-card border bg-hull p-4',
        'text-h3 transition-colors duration-fast active:bg-deck',
        tone === 'danger' ? 'border-danger text-danger' : 'border-line text-ink',
      )}
    >
      {icon}
      {label}
    </button>
  )
}
