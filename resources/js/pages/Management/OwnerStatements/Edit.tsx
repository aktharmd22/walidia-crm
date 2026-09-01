import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Management/OwnerStatements/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { agreements?: Option[]; yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { agreements?: Option[]; yachts?: Option[] })}
      initial={{
        management_agreement_id: fv(record.management_agreement_id),
        yacht_id: fv(record.yacht_id),
        period_start: fv(record.period_start),
        period_end: fv(record.period_end),
        charter_revenue: fv(record.charter_revenue, 0),
        management_fee: fv(record.management_fee, 0),
        operating_costs: fv(record.operating_costs, 0),
        maintenance_costs: fv(record.maintenance_costs, 0),
        crew_costs: fv(record.crew_costs, 0),
        currency: fv(record.currency, 'AED'),
        status: fv(record.status, 'draft'),
        notes: fv(record.notes),
      }}
      action={`/management/owner-statements/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/management/owner-statements/${record.id}`}
    />
  )
}

export type { Option }
