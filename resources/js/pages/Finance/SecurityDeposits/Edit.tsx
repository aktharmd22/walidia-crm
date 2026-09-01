import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { depositSections } from '@/pages/Finance/SecurityDeposits/Create'

interface Option {
  value: string | number
  label: string
}

export default function SecurityDepositEdit({
  record,
  bookings = [],
}: {
  record: Record<string, unknown> & { id: number }
  bookings?: Option[]
}) {
  return (
    <ResourceForm
      title="Edit security deposit"
      description="Amounts are audited: who changed the held figure, and when."
      sections={depositSections(bookings)}
      initial={{
        booking_id: fv(record.booking_id),
        amount: fv(record.amount),
        currency: fv(record.currency, 'AED'),
        method: fv(record.method, 'card_hold'),
      }}
      action={`/finance/security-deposits/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/finance/security-deposits/${record.id}`}
    />
  )
}
