import { PortalShell } from '@/components/portal/PortalShell'
import { Card, CardBody, CardHeader, CardTitle, DateText, Money } from '@/ui/Primitives'

interface Statement {
  reference: string | null
  yacht: string | null
  scope: string | null
  period_start: string | null
  period_end: string | null
  charter_revenue: string
  management_fee: string
  operating_costs: string
  maintenance_costs: string
  crew_costs: string
  net_to_owner: string
  currency: string
  issued_at: string | null
  status: string
}

/**
 * What an owner sees. One period, their own numbers, and the arithmetic shown
 * rather than asserted — an owner should be able to check the total themselves.
 */
export default function OwnerStatementPortal({
  statement,
  expires_at,
}: {
  statement: Statement
  expires_at: string | null
}) {
  const lines: { label: string; amount: string; deduction?: boolean }[] = [
    { label: 'Charter revenue', amount: statement.charter_revenue },
    { label: 'Management fee', amount: statement.management_fee, deduction: true },
    { label: 'Operating costs', amount: statement.operating_costs, deduction: true },
    { label: 'Maintenance', amount: statement.maintenance_costs, deduction: true },
    { label: 'Crew', amount: statement.crew_costs, deduction: true },
  ]

  return (
    <PortalShell
      title={statement.yacht ?? 'Owner statement'}
      eyebrow={statement.reference}
      expiresAt={expires_at}
    >
      <Card>
        <CardHeader>
          <CardTitle>
            <DateText value={statement.period_start} /> – <DateText value={statement.period_end} />
          </CardTitle>
        </CardHeader>

        <ul className="divide-y divide-line">
          {lines.map((line) => (
            <li key={line.label} className="flex items-center justify-between gap-4 px-5 py-3">
              <span className="text-body text-ink">{line.label}</span>
              <span className={line.deduction ? 'text-ink-soft' : 'text-ink'}>
                {line.deduction && '− '}
                <Money amount={line.amount} currency={statement.currency} withCurrency={false} />
              </span>
            </li>
          ))}
          <li className="flex items-center justify-between gap-4 bg-deck px-5 py-4">
            <span className="text-h3 text-ink">Net to you</span>
            <span className="text-h2 text-ink">
              <Money amount={statement.net_to_owner} currency={statement.currency} />
            </span>
          </li>
        </ul>
      </Card>

      <Card>
        <CardBody>
          <p className="text-small text-ink-soft">
            Issued <DateText value={statement.issued_at} />. Questions about any line on this statement go to your
            account manager, who can show you the charters and invoices behind it.
          </p>
        </CardBody>
      </Card>
    </PortalShell>
  )
}
