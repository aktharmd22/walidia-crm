import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/BuyerRequirements/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Edit requirement"
      sections={sections(props as { clients?: Option[] })}
      initial={{
        client_id: fv(record.client_id),
        budget_min: fv(record.budget_min),
        budget_max: fv(record.budget_max),
        currency: fv(record.currency, 'EUR'),
        loa_min: fv(record.loa_min),
        loa_max: fv(record.loa_max),
        year_from: fv(record.year_from),
        use_case: fv(record.use_case),
        status: fv(record.status, 'active'),
        notes: fv(record.notes),
      }}
      action={`/brokerage/buyer-requirements/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/buyer-requirements/${record.id}`}
    />
  )
}

export type { Option }
