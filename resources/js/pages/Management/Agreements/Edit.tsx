import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Management/Agreements/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        scope: fv(record.scope, 'full'),
        fee_model: fv(record.fee_model, 'fixed'),
        monthly_fee: fv(record.monthly_fee),
        fee_percentage: fv(record.fee_percentage),
        currency: fv(record.currency, 'AED'),
        starts_on: fv(record.starts_on),
        ends_on: fv(record.ends_on),
        notice_days: fv(record.notice_days, 90),
        opex_budget_annual: fv(record.opex_budget_annual),
        status: fv(record.status, 'active'),
        notes: fv(record.notes),
      }}
      action={`/management/agreements/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/management/agreements/${record.id}`}
    />
  )
}

export type { Option }
