import { Link } from '@inertiajs/react'
import { Compass, FileText } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState, Money, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { EnquiryRow } from '@/pages/Charter/Enquiries/Index'
import type { StatusTone } from '@/types'

interface ProposalSummary {
  id: number
  reference: string
  version: number
  total: string
  currency: string
  status: string
  status_tone: StatusTone
  valid_until: string | null
  url: string
}

export default function EnquiryShow({
  record,
  timeline = [],
  proposals = [],
  can,
}: {
  record: EnquiryRow & {
    duration_hours: string | null
    itinerary_notes: string | null
    notes: string | null
    budget_min: string | null
    guests_adults: number
    guests_children: number
  }
  timeline?: TimelineEntry[]
  proposals?: ProposalSummary[]
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.client?.name ?? record.reference}
      subtitle={`${record.reference} · ${record.experience_type?.replace(/_/g, ' ') ?? 'charter'}`}
      status={record.status}
      statusTone={record.status_tone}
      editUrl={can.update ? `/charter/enquiries/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/charter/enquiries/${record.id}` : undefined}
      backUrl="/charter/enquiries"
      actions={[
        <Link key="match" href={`/charter/enquiries/${record.id}/matching`}>
          <Button variant="primary" icon={<Compass className="size-4" />}>
            Find yachts
          </Button>
        </Link>,
      ]}
      facts={[
        { label: 'Reference', value: <span className="numeric">{record.reference}</span> },
        { label: 'Requested', value: <DateText value={record.requested_date} /> },
        { label: 'Duration', value: record.duration_hours ? <span className="numeric">{record.duration_hours} h</span> : '—' },
        { label: 'Adults', value: <Num value={record.guests_adults} /> },
        { label: 'Children', value: <Num value={record.guests_children} /> },
        { label: 'Marina', value: record.marina?.name ?? '—' },
        {
          label: 'Budget',
          value: record.budget_max ? (
            <span>
              <Money amount={record.budget_min ?? '0'} currency={record.currency} withCurrency={false} /> –{' '}
              <Money amount={record.budget_max} currency={record.currency} />
            </span>
          ) : (
            '—'
          ),
        },
        { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
        { label: 'KYC', value: record.client?.kyc_status?.replace('_', ' ') ?? '—' },
      ]}
      timeline={timeline}
      aside={
        <Card>
          <CardHeader>
            <CardTitle>Proposals</CardTitle>
            <Link href={`/charter/proposals/create?enquiry=${record.id}`} className="text-small text-accent-ink hover:underline">
              New
            </Link>
          </CardHeader>
          {proposals.length === 0 ? (
            <EmptyState
              icon={<FileText className="size-5" aria-hidden />}
              title="No proposals yet"
              description="Shortlist a yacht from matching, then price it."
            />
          ) : (
            <ul className="divide-y divide-line">
              {proposals.map((proposal) => (
                <li key={proposal.id}>
                  <Link href={proposal.url} className="flex items-center justify-between gap-3 px-5 py-3 hover:bg-deck">
                    <span className="min-w-0">
                      <span className="block text-h3 text-ink">
                        v{proposal.version} · {proposal.reference}
                      </span>
                      <span className="block text-small text-ink-faint">
                        valid to <DateText value={proposal.valid_until} />
                      </span>
                    </span>
                    <span className="text-end">
                      <Money amount={proposal.total} currency={proposal.currency} className="block" />
                      <StatusPill tone={proposal.status_tone}>{proposal.status}</StatusPill>
                    </span>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </Card>
      }
    >
      {record.itinerary_notes && (
        <Card>
          <CardHeader>
            <CardTitle>What they asked for</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.itinerary_notes}</p>
          </CardBody>
        </Card>
      )}

      {record.notes && (
        <Card>
          <CardHeader>
            <CardTitle>Internal notes</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.notes}</p>
          </CardBody>
        </Card>
      )}
    </DetailShell>
  )
}
