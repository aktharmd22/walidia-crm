import { PortalShell } from '@/components/portal/PortalShell'
import { Card, CardBody, CardHeader, CardTitle, Money, Num } from '@/ui/Primitives'

interface Listing {
  reference: string | null
  yacht: string | null
  builder: string | null
  year_built: number | null
  loa_m: string | null
  guests: number | null
  asking_price: string
  currency: string
  commission_rate: string
  mandate_type: string
  requires_nda: boolean
  requires_proof_of_funds: boolean
  marketing_summary: string | null
  status: string
}

/**
 * A partner broker's view. Everything they need to bring a buyer, and nothing
 * about the seller — no reserve, no owner, no history.
 */
export default function ListingPortal({ listing, expires_at }: { listing: Listing; expires_at: string | null }) {
  return (
    <PortalShell title={listing.yacht ?? 'Listing'} eyebrow={listing.reference} expiresAt={expires_at}>
      <Card>
        <CardBody>
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
              <span className="block text-micro uppercase tracking-wide text-ink-faint">Asking</span>
              <span className="text-h1 text-ink">
                <Money amount={listing.asking_price} currency={listing.currency} />
              </span>
            </div>
            <div className="text-end">
              <span className="block text-micro uppercase tracking-wide text-ink-faint">Co-brokerage</span>
              <span className="numeric text-h3 text-ink">{listing.commission_rate}% shared</span>
            </div>
          </div>
        </CardBody>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>The yacht</CardTitle>
        </CardHeader>
        <CardBody>
          <dl className="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Builder</dt>
              <dd className="text-body text-ink">{listing.builder ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Year</dt>
              <dd className="numeric text-body text-ink">{listing.year_built ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Length</dt>
              <dd className="numeric text-body text-ink">{listing.loa_m ? `${listing.loa_m} m` : '—'}</dd>
            </div>
            <div>
              <dt className="text-micro uppercase tracking-wide text-ink-faint">Guests</dt>
              <dd className="text-body text-ink">{listing.guests ? <Num value={listing.guests} /> : '—'}</dd>
            </div>
          </dl>
        </CardBody>
      </Card>

      {listing.marketing_summary && (
        <Card>
          <CardBody>
            <p className="whitespace-pre-wrap text-body text-ink">{listing.marketing_summary}</p>
          </CardBody>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Before a viewing</CardTitle>
        </CardHeader>
        <CardBody>
          <ul className="flex list-disc flex-col gap-2 ps-5 text-body text-ink-soft">
            {listing.requires_nda && <li>Your buyer signs an NDA before boarding. We will send it on request.</li>}
            {listing.requires_proof_of_funds && <li>Proof of funds is required before an offer reaches the seller.</li>}
            <li>Viewings are arranged through us, with the captain, at the yacht's berth.</li>
          </ul>
        </CardBody>
      </Card>
    </PortalShell>
  )
}
