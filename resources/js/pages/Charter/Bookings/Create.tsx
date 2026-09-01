import { Head, Link } from '@inertiajs/react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, EmptyState } from '@/ui/Primitives'

/**
 * Bookings are opened by accepting a proposal, not created by hand: that is
 * what locks the yacht and lays down the payment schedule.
 */
export default function BookingCreate() {
  return (
    <>
      <Head title="New booking" />
      <PageHeader title="New booking" />
      <Card>
        <EmptyState
          title="A booking opens when a proposal is accepted"
          description="Accepting is what holds the yacht in the fleet calendar and creates the deposit the release gate reads."
          action={
            <Link href="/charter/proposals">
              <Button variant="primary">Open proposals</Button>
            </Link>
          }
        />
      </Card>
    </>
  )
}
