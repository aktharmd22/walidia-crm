import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { damageSections } from '@/pages/Charter/DamageReports/Create'

interface Option {
  value: string | number
  label: string
}

export default function DamageReportEdit({
  record,
  bookings = [],
  yachts = [],
}: {
  record: Record<string, unknown> & { id: number; reference: string | null }
  bookings?: Option[]
  yachts?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.reference ?? 'damage report'}`}
      sections={damageSections(bookings, yachts)}
      initial={{
        booking_id: fv(record.booking_id),
        yacht_id: fv(record.yacht_id),
        discovered_at: String(fv(record.discovered_at)).slice(0, 16),
        description: fv(record.description),
        estimated_cost: fv(record.estimated_cost),
        deduct_from_deposit: fv(record.deduct_from_deposit, false),
      }}
      action={`/charter/damage-reports/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/charter/damage-reports/${record.id}`}
    />
  )
}
