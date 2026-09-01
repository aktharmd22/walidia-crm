import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { transactionSections, type Option } from '@/pages/Brokerage/Transactions/Create'

export default function Edit({
  record,
  ...props
}: {
  record: Record<string, unknown> & { id: number }
  listings?: Option[]
  clients?: Option[]
  owners?: Option[]
}) {
  return (
    <ResourceForm
      title="Edit transaction"
      sections={transactionSections(props)}
      initial={{
        listing_id: fv(record.listing_id),
        buyer_client_id: fv(record.buyer_client_id),
        seller_owner_id: fv(record.seller_owner_id),
        agreed_price: fv(record.agreed_price),
        currency: fv(record.currency, 'EUR'),
        deposit_amount: fv(record.deposit_amount),
        balance_amount: fv(record.balance_amount),
        contract_type: fv(record.contract_type, 'myba'),
        escrow_agent: fv(record.escrow_agent),
        contract_signed_on: fv(record.contract_signed_on),
        expected_closing_on: fv(record.expected_closing_on),
        status: fv(record.status, 'draft'),
        notes: fv(record.notes),
      }}
      action={`/brokerage/transactions/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/brokerage/transactions/${record.id}`}
    />
  )
}
