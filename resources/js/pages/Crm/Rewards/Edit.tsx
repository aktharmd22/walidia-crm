import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Crm/Rewards/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { clients?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { clients?: Option[] })}
      initial={{
        client_id: fv(record.client_id),
        type: fv(record.type, 'voucher'),
        value: fv(record.value),
        currency: fv(record.currency, 'AED'),
        points: fv(record.points),
        valid_from: fv(record.valid_from),
        expires_on: fv(record.expires_on),
        status: fv(record.status, 'issued'),
        description: fv(record.description),
      }}
      action={`/crm/rewards/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/crm/rewards/${record.id}`}
    />
  )
}

export type { Option }
