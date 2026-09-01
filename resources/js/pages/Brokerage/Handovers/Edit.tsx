import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Brokerage/Handovers/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { transactions?: Option[]; marinas?: Option[] }) {
  return (
    <ResourceForm
      title="Edit handover"
      sections={sections(props as { transactions?: Option[]; marinas?: Option[] })}
      initial={{
        transaction_id: fv(record.transaction_id),
        marina_id: fv(record.marina_id),
        scheduled_at: String(fv(record.scheduled_at)).slice(0, 16),
        keys_handed_over: fv(record.keys_handed_over, false),
        documents_handed_over: fv(record.documents_handed_over, false),
        inventory_signed: fv(record.inventory_signed, false),
        flag_registration_updated: fv(record.flag_registration_updated, false),
        insurance_transferred: fv(record.insurance_transferred, false),
        outstanding_items: fv(record.outstanding_items),
        status: fv(record.status, 'pending'),
      }}
      action={`/brokerage/handovers/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/handovers/${record.id}`}
    />
  )
}

export type { Option }
