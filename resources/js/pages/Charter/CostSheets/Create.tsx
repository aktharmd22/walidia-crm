import { Head, Link } from '@inertiajs/react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, EmptyState } from '@/ui/Primitives'

/**
 * A cost sheet belongs to a booking and is created from it — there is no
 * standalone one, so this screen points where it actually happens.
 */
export default function CostSheetCreate() {
  return (
    <>
      <Head title="New cost sheet" />
      <PageHeader title="New cost sheet" />
      <Card>
        <EmptyState
          title="Cost sheets are created from a booking"
          description="Open the booking and use Cost sheet — that way the quote starts from what the client actually accepted."
          action={
            <Link href="/charter/bookings">
              <Button variant="primary">Open bookings</Button>
            </Link>
          }
        />
      </Card>
    </>
  )
}
