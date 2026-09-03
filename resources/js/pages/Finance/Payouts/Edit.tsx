import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { sections, type Option } from '@/pages/Finance/Payouts/Create'

export default function Edit({
  record,
  ...props
}: { record: Record<string, unknown> & { id: number } } & { clients?: Option[]; vendors?: Option[] }) {
  return (
    <ResourceForm
      title="Edit"
      sections={sections(props as { clients?: Option[]; vendors?: Option[] })}
      initial={{
        type: fv(record.type, 'seller'),
        payee_name: fv(record.payee_name),
        payee_client_id: fv(record.payee_client_id),
        payee_vendor_id: fv(record.payee_vendor_id),
        amount: fv(record.amount),
        currency: fv(record.currency, 'AED'),
        method: fv(record.method, 'bank_transfer'),
        due_on: fv(record.due_on),
        status: fv(record.status, 'pending'),
        notes: fv(record.notes),
      }}
      action={`/finance/payouts/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/finance/payouts/${record.id}`}
    />
  )
}

export type { Option }
