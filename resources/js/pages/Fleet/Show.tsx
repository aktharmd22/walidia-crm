import { Link } from '@inertiajs/react'
import { Anchor, CalendarRange } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'

interface YachtRecord {
  id: number
  reference: string | null
  name: string
  name_ar: string | null
  builder: string | null
  model: string | null
  year_built: number | null
  year_refit: number | null
  loa_m: string | null
  beam_m: string | null
  draft_m: string | null
  gross_tonnage: number | null
  engines: string | null
  engine_hours: number | null
  cruising_speed_kn: number | null
  max_speed_kn: number | null
  capacity_static: number | null
  capacity_cruising: number | null
  cabins: number | null
  berths: number | null
  crew_count: number | null
  flag_country: string | null
  registration_no: string | null
  imo_no: string | null
  roles: string[]
  status: string
  status_tone: StatusTone
  home_marina?: { id: number; name: string; timezone: string } | null
  charter_rates?: {
    hourly_rate: string | null
    half_day_rate: string | null
    full_day_rate: string | null
    overnight_rate: string | null
    currency: string
    min_hours: number
    is_bookable: boolean
  }
  asking_price?: string | null
  description: string | null
}

export default function YachtShow({
  record,
  timeline = [],
  can,
}: {
  record: YachtRecord
  timeline?: TimelineEntry[]
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.name}
      subtitle={[record.builder, record.model, record.year_built].filter(Boolean).join(' · ') || record.reference}
      status={record.status.replace('_', ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/fleet/yachts/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/fleet/yachts/${record.id}` : undefined}
      backUrl="/fleet/yachts"
      actions={[
        <Link key="availability" href="/fleet/availability">
          <Button variant="secondary" icon={<CalendarRange className="size-4" />}>
            Availability
          </Button>
        </Link>,
      ]}
      facts={[
        { label: 'Reference', value: <span className="numeric">{record.reference ?? '—'}</span> },
        { label: 'LOA', value: record.loa_m ? <span className="numeric">{record.loa_m} m</span> : '—' },
        { label: 'Beam', value: record.beam_m ? <span className="numeric">{record.beam_m} m</span> : '—' },
        { label: 'Draft', value: record.draft_m ? <span className="numeric">{record.draft_m} m</span> : '—' },
        { label: 'Gross tonnage', value: <Num value={record.gross_tonnage ?? 0} /> },
        { label: 'Flag', value: record.flag_country ?? '—' },
        { label: 'Registration', value: <span className="numeric">{record.registration_no ?? '—'}</span> },
        { label: 'IMO', value: <span className="numeric">{record.imo_no ?? '—'}</span> },
        { label: 'Home marina', value: record.home_marina?.name ?? '—' },
        { label: 'Timezone', value: record.home_marina?.timezone ?? 'Asia/Dubai' },
      ]}
      timeline={timeline}
      aside={
        record.charter_rates ? (
          <Card>
            <CardHeader>
              <CardTitle>Charter rates</CardTitle>
              <StatusPill tone={record.charter_rates.is_bookable ? 'success' : 'neutral'}>
                {record.charter_rates.is_bookable ? 'Bookable' : 'Not bookable'}
              </StatusPill>
            </CardHeader>
            <CardBody>
              <dl className="flex flex-col gap-3">
                <Rate label="Hourly" amount={record.charter_rates.hourly_rate} currency={record.charter_rates.currency} />
                <Rate label="Half day" amount={record.charter_rates.half_day_rate} currency={record.charter_rates.currency} />
                <Rate label="Full day" amount={record.charter_rates.full_day_rate} currency={record.charter_rates.currency} />
                <Rate label="Overnight" amount={record.charter_rates.overnight_rate} currency={record.charter_rates.currency} />
                <div className="flex items-center justify-between gap-4">
                  <dt className="text-small text-ink-faint">Minimum</dt>
                  <dd className="text-body text-ink">
                    <Num value={record.charter_rates.min_hours} /> hours
                  </dd>
                </div>
              </dl>
            </CardBody>
          </Card>
        ) : undefined
      }
    >
      <Card>
        <CardHeader>
          <CardTitle>Specification</CardTitle>
          <span className="flex gap-1">
            {record.roles.map((role) => (
              <span key={role} className="rounded-pill bg-deck px-2 py-px text-micro text-ink-soft">
                {role}
              </span>
            ))}
          </span>
        </CardHeader>
        <CardBody>
          <dl className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Spec label="Guests cruising" value={record.capacity_cruising} />
            <Spec label="Guests static" value={record.capacity_static} />
            <Spec label="Cabins" value={record.cabins} />
            <Spec label="Berths" value={record.berths} />
            <Spec label="Crew" value={record.crew_count} />
            <Spec label="Cruising speed" value={record.cruising_speed_kn} suffix=" kn" />
            <Spec label="Max speed" value={record.max_speed_kn} suffix=" kn" />
            <Spec label="Engine hours" value={record.engine_hours} />
          </dl>
          {record.engines && (
            <p className="mt-4 text-body text-ink-soft">
              <span className="text-ink-faint">Engines: </span>
              {record.engines}
            </p>
          )}
        </CardBody>
      </Card>

      {record.asking_price && (
        <Card>
          <CardHeader>
            <CardTitle>For sale</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="text-display text-ink">
              <Money amount={record.asking_price} />
            </p>
          </CardBody>
        </Card>
      )}

      {record.description ? (
        <Card>
          <CardHeader>
            <CardTitle>Description</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.description}</p>
          </CardBody>
        </Card>
      ) : (
        <Card>
          <EmptyState
            icon={<Anchor className="size-5" aria-hidden />}
            title="No description yet"
            description="A description here is what the website sync publishes."
          />
        </Card>
      )}
    </DetailShell>
  )
}

function Spec({ label, value, suffix = '' }: { label: string; value: number | null; suffix?: string }) {
  return (
    <div>
      <dt className="text-small text-ink-faint">{label}</dt>
      <dd className="text-h2 text-ink">
        {value === null ? '—' : (
          <>
            <Num value={value} />
            {suffix}
          </>
        )}
      </dd>
    </div>
  )
}

function Rate({ label, amount, currency }: { label: string; amount: string | null; currency: string }) {
  return (
    <div className="flex items-center justify-between gap-4">
      <dt className="text-small text-ink-faint">{label}</dt>
      <dd className="text-body text-ink">
        {amount ? <Money amount={amount} currency={currency} /> : '—'}
      </dd>
    </div>
  )
}
