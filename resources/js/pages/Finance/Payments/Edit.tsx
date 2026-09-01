import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { paymentSections } from '@/pages/Finance/Payments/Create'

interface Option {
  value: string | number
  label: string
}

export default function PaymentEdit({
  record,
  clients = [],
}: {
  record: Record<string, unknown> & { id: number; reference: string | null }
  clients?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.reference ?? 'payment'}`}
      description="A reconciled payment cannot be edited — it is refunded instead."
      sections={paymentSections(clients)}
      initial={{
        client_id: (record.client as { id: number } | null)?.id ?? '',
        method: fv(record.method, 'bank_transfer'),
        amount: fv(record.amount),
        currency: fv(record.currency, 'AED'),
        exchange_rate: fv(record.exchange_rate, 1),
        received_at: String(record.received_at ?? '').slice(0, 16),
        gateway_reference: fv(record.gateway_reference),
        bank_charge_amount: fv(record.bank_charge_amount),
        notes: fv(record.notes),
      }}
      action={`/finance/payments/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/finance/payments/${record.id}`}
    />
  )
}
