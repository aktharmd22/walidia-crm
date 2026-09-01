import { useState } from 'react'
import { router, useForm } from '@inertiajs/react'
import { BadgeCheck, Star } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardHeader, CardTitle, DateText, EmptyState, Num } from '@/ui/Primitives'
import { Drawer } from '@/ui/Overlays'
import { Select, Textarea } from '@/ui/Field'
import type { VendorRow } from '@/pages/Vendors/Index'

interface Rating {
  id: number
  score: number
  comment: string | null
  booking: string | null
  created_at: string | null
}

/** Ratings are how the ops team chooses a supplier under time pressure. */
export default function VendorShow({
  record,
  ratings = [],
  can,
}: {
  record: VendorRow & {
    trn: string | null
    trade_licence_no: string | null
    licence_expiry: string | null
    notes: string | null
  }
  ratings?: Rating[]
  can: { update?: boolean; delete?: boolean; approve?: boolean }
}) {
  const [rating, setRating] = useState(false)
  const form = useForm({ score: '5', comment: '' })

  return (
    <>
      <DetailShell
        title={record.display_name}
        subtitle={record.category ?? 'Uncategorised'}
        status={record.is_approved ? 'Approved' : 'Not approved'}
        statusTone={record.is_approved ? 'success' : 'warning'}
        editUrl={can.update ? `/vendors/${record.id}/edit` : undefined}
        archiveUrl={can.delete ? `/vendors/${record.id}` : undefined}
        backUrl="/vendors"
        actions={[
          can.approve && !record.is_approved ? (
            <Button
              key="approve"
              variant="primary"
              icon={<BadgeCheck className="size-4" />}
              onClick={() => router.post(`/vendors/${record.id}/approve`, {}, { preserveScroll: true })}
            >
              Approve vendor
            </Button>
          ) : null,
          can.update ? (
            <Button key="rate" variant="secondary" icon={<Star className="size-4" />} onClick={() => setRating(true)}>
              Rate
            </Button>
          ) : null,
        ]}
        facts={[
          { label: 'Contact', value: record.contact_name ?? '—' },
          { label: 'Mobile', value: <span className="numeric">{record.mobile ?? '—'}</span> },
          { label: 'Email', value: record.email ?? '—' },
          { label: 'TRN', value: <span className="numeric">{record.trn ?? '—'}</span> },
          { label: 'Licence', value: <span className="numeric">{record.trade_licence_no ?? '—'}</span> },
          { label: 'Licence expiry', value: <DateText value={record.licence_expiry} /> },
          { label: 'Payment terms', value: record.payment_terms_days ? `${record.payment_terms_days} days` : '—' },
          { label: 'Rating', value: record.rating_avg ? <Num value={record.rating_avg} fractionDigits={1} /> : 'Unrated' },
        ]}
      >
        {!record.is_approved && (
          <p className="rounded-card border border-warning bg-warning-bg px-4 py-3 text-small text-warning">
            Purchase orders cannot be raised against an unapproved vendor.
          </p>
        )}

        <Card>
          <CardHeader>
            <CardTitle>How they have performed</CardTitle>
          </CardHeader>
          {ratings.length === 0 ? (
            <EmptyState
              icon={<Star className="size-5" aria-hidden />}
              title="No ratings yet"
              description="Rate a vendor after a charter and the score follows them to the next one."
            />
          ) : (
            <ul className="divide-y divide-line">
              {ratings.map((entry) => (
                <li key={entry.id} className="px-5 py-3">
                  <div className="flex items-center justify-between gap-3">
                    <span className="numeric text-h3 text-ink">{entry.score} / 5</span>
                    <span className="text-small text-ink-faint">
                      {entry.booking ?? '—'} · <DateText value={entry.created_at} />
                    </span>
                  </div>
                  {entry.comment && <p className="mt-1 text-small text-ink-soft">{entry.comment}</p>}
                </li>
              ))}
            </ul>
          )}
        </Card>
      </DetailShell>

      <Drawer
        open={rating}
        onOpenChange={setRating}
        title="Rate this vendor"
        footer={
          <>
            <Button variant="secondary" onClick={() => setRating(false)}>
              Cancel
            </Button>
            <Button
              variant="primary"
              loading={form.processing}
              onClick={() =>
                form.post(`/vendors/${record.id}/ratings`, {
                  preserveScroll: true,
                  onSuccess: () => {
                    form.reset()
                    setRating(false)
                  },
                })
              }
            >
              Save rating
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Select
            label="Score"
            required
            value={form.data.score}
            onChange={(event) => form.setData('score', event.target.value)}
            options={[5, 4, 3, 2, 1].map((score) => ({ value: String(score), label: `${score} / 5` }))}
          />
          <Textarea
            label="What happened"
            rows={4}
            value={form.data.comment}
            onChange={(event) => form.setData('comment', event.target.value)}
            help="Written for the next person choosing a supplier at short notice."
          />
        </div>
      </Drawer>
    </>
  )
}
