import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Inspections/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[]; listings?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        listing_id: fv(record.listing_id),
        type: fv(record.type, 'listing'),
        inspected_at: String(fv(record.inspected_at)).slice(0, 16),
        status: fv(record.status, 'scheduled'),
        hull_condition: fv(record.hull_condition),
        engine_condition: fv(record.engine_condition),
        interior_condition: fv(record.interior_condition),
        systems_condition: fv(record.systems_condition),
        outcome: fv(record.outcome),
        estimated_works_cost: fv(record.estimated_works_cost),
        findings: fv(record.findings),
        recommended_works: fv(record.recommended_works),
      }}
      action={`/brokerage/inspections/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/inspections/${record.id}`}
    />
  )
}

export type { Option }
