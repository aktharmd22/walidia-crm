import { Head, Link } from '@inertiajs/react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, EmptyState } from '@/ui/Primitives'

/** Lines are edited in place on the cost sheet itself, phase by phase. */
export default function CostSheetEdit({ record }: { record: { id: number; reference: string } }) {
  return (
    <>
      <Head title={`Edit ${record.reference}`} />
      <PageHeader title={record.reference} />
      <Card>
        <EmptyState
          title="Edit the lines on the sheet"
          description="Each phase — quoted, invoiced, actual — is edited in place, and what you may change depends on your role."
          action={
            <Link href={`/charter/cost-sheets/${record.id}`}>
              <Button variant="primary">Open cost sheet</Button>
            </Link>
          }
        />
      </Card>
    </>
  )
}
