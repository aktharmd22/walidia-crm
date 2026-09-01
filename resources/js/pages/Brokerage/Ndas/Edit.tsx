import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Ndas/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { clients?: Option[]; listings?: Option[] }) {
  return (
    <ResourceForm
      title="Edit NDA"
      sections={sections(props as { clients?: Option[]; listings?: Option[] })}
      initial={{
        client_id: fv(record.client_id),
        listing_id: fv(record.listing_id),
        scope: fv(record.scope, 'listing'),
        sent_at: String(fv(record.sent_at)).slice(0, 16),
        expires_on: fv(record.expires_on),
        status: fv(record.status, 'draft'),
      }}
      action={`/brokerage/ndas/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/ndas/${record.id}`}
    />
  )
}

export type { Option }
