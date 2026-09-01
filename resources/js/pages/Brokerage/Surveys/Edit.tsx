import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Surveys/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { listings?: Option[] }) {
  return (
    <ResourceForm
      title="Edit survey"
      sections={sections(props as { listings?: Option[] })}
      initial={{
        listing_id: fv(record.listing_id),
        type: fv(record.type, 'condition'),
        surveyor_name: fv(record.surveyor_name),
        surveyor_company: fv(record.surveyor_company),
        scheduled_at: String(fv(record.scheduled_at)).slice(0, 16),
        cost: fv(record.cost),
        paid_by: fv(record.paid_by, 'buyer'),
        status: fv(record.status, 'scheduled'),
      }}
      action={`/brokerage/surveys/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/surveys/${record.id}`}
    />
  )
}

export type { Option }
