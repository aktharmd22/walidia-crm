import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Management/Maintenance/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { yachts?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { yachts?: Option[]; vendors?: Option[] })}
      initial={{
        yacht_id: fv(record.yacht_id),
        title: fv(record.title),
        category: fv(record.category, 'routine'),
        priority: fv(record.priority, 'normal'),
        vendor_id: fv(record.vendor_id),
        due_on: fv(record.due_on),
        estimated_cost: fv(record.estimated_cost),
        currency: fv(record.currency, 'AED'),
        owner_approval_required: fv(record.owner_approval_required, false),
        blocks_charter: fv(record.blocks_charter, false),
        status: fv(record.status, 'open'),
        description: fv(record.description),
      }}
      action={`/management/maintenance/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/management/maintenance/${record.id}`}
    />
  )
}

export type { Option }
