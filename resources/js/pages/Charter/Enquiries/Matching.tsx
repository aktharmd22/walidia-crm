import { Head, Link, router } from '@inertiajs/react'
import { Compass, RefreshCw, Star } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { cn } from '@/lib/cn'
import type { EnquiryRow } from '@/pages/Charter/Enquiries/Index'

interface Reason {
  factor: string
  detail: string
  weight: number
}

interface Match {
  id: number
  score: number
  reasons: Reason[]
  is_shortlisted: boolean
  yacht: {
    id: number
    name: string
    builder: string | null
    loa_m: string | null
    capacity_cruising: number | null
    marina: string | null
    day_rate: string | null
    currency: string
    url: string
  }
}

/**
 * Matching, with its reasoning visible.
 *
 * Every score can be defended to a client: the factors that produced it are
 * listed under each yacht, and availability is a hard filter rather than a
 * scoring nudge — a yacht that is already out never appears.
 */
export default function Matching({ record, matches = [] }: { record: EnquiryRow; matches?: Match[] }) {
  return (
    <>
      <Head title={`Matching — ${record.reference}`} />

      <PageHeader
        title="Yacht matching"
        description={`${record.guest_count} guests · ${record.experience_type?.replace(/_/g, ' ') ?? 'charter'} · ${
          record.requested_date ?? 'dates open'
        }`}
        actions={
          <>
            <Link href={record.url}>
              <Button variant="secondary">Back to enquiry</Button>
            </Link>
            <Button
              variant="primary"
              icon={<RefreshCw className="size-4" />}
              onClick={() => router.get(`/charter/enquiries/${record.id}/matching`, { refresh: true })}
            >
              Re-run matching
            </Button>
          </>
        }
      />

      {matches.length === 0 ? (
        <Card>
          <EmptyState
            icon={<Compass className="size-5" aria-hidden />}
            title="Nothing available for these dates"
            description="Every bookable yacht is either occupied for this window or cannot take this many guests."
          />
        </Card>
      ) : (
        <div className="grid gap-4 lg:grid-cols-2">
          {matches.map((match) => (
            <Card key={match.id} className={cn(match.is_shortlisted && 'border-accent')}>
              <CardBody className="flex flex-col gap-4">
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0">
                    <Link href={match.yacht.url} className="text-h2 text-ink hover:text-accent-ink">
                      {match.yacht.name}
                    </Link>
                    <p className="text-small text-ink-faint">
                      {[match.yacht.builder, match.yacht.loa_m ? `${match.yacht.loa_m} m` : null, match.yacht.marina]
                        .filter(Boolean)
                        .join(' · ')}
                    </p>
                  </div>

                  <div className="text-end">
                    <p
                      className={cn(
                        'numeric text-display',
                        match.score >= 75 ? 'text-success' : match.score >= 50 ? 'text-info' : 'text-ink-faint',
                      )}
                    >
                      {match.score}
                    </p>
                    <p className="text-micro text-ink-faint">match score</p>
                  </div>
                </div>

                <div className="flex flex-wrap items-center gap-4">
                  {match.yacht.day_rate && (
                    <span>
                      <span className="block text-micro text-ink-faint">Day rate</span>
                      <Money amount={match.yacht.day_rate} currency={match.yacht.currency} />
                    </span>
                  )}
                  <span>
                    <span className="block text-micro text-ink-faint">Cruising guests</span>
                    <Num value={match.yacht.capacity_cruising ?? 0} className="text-body text-ink" />
                  </span>
                </div>

                <ul className="flex flex-col gap-2">
                  {match.reasons.map((reason) => (
                    <li key={reason.factor} className="flex items-start gap-2 text-small">
                      <StatusPill tone={reason.weight >= 0 ? 'success' : 'danger'}>
                        {reason.weight > 0 ? `+${reason.weight}` : reason.weight}
                      </StatusPill>
                      <span className="text-ink-soft">{reason.detail}</span>
                    </li>
                  ))}
                </ul>

                <div className="flex gap-2">
                  <Button
                    variant={match.is_shortlisted ? 'primary' : 'secondary'}
                    size="sm"
                    icon={<Star className="size-4" />}
                    onClick={() =>
                      router.post(
                        `/charter/enquiries/${record.id}/shortlist`,
                        { match_id: match.id, shortlisted: !match.is_shortlisted },
                        { preserveScroll: true },
                      )
                    }
                  >
                    {match.is_shortlisted ? 'Shortlisted' : 'Shortlist'}
                  </Button>
                  <Link href={`/charter/proposals/create?enquiry=${record.id}&yacht=${match.yacht.id}`}>
                    <Button variant="ghost" size="sm">
                      Price a proposal
                    </Button>
                  </Link>
                </div>
              </CardBody>
            </Card>
          ))}
        </div>
      )}
    </>
  )
}
