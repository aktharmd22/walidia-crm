import { Link } from '@inertiajs/react'
import { Users } from 'lucide-react'
import { DetailShell, type TimelineEntry } from '@/components/crud/DetailShell'
import { Card, CardHeader, CardTitle, DateText, EmptyState, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import type { StatusTone } from '@/types'
import type { ClientRow } from '@/pages/Clients/Index'

interface CompanyRecord {
  id: number
  reference: string | null
  legal_name: string
  trade_name: string | null
  display_name: string
  type: string
  email: string | null
  phone: string | null
  website: string | null
  city: string | null
  country: string | null
  trn?: string
  trade_licence_no: string | null
  licence_expiry: string | null
  licence_expiring: boolean
  payment_terms_days: number
  commission_rate_default: string | null
  status: string
  status_tone: StatusTone
}

interface Contact {
  id: number
  name: string
  position: string | null
  email: string | null
  mobile: string | null
  is_primary: boolean
}

export default function CompanyShow({
  record,
  clients = [],
  contacts = [],
  timeline = [],
  can,
}: {
  record: CompanyRecord
  clients?: ClientRow[]
  contacts?: Contact[]
  timeline?: TimelineEntry[]
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.display_name}
      subtitle={record.trade_name && record.trade_name !== record.legal_name ? record.legal_name : record.reference}
      status={record.status}
      statusTone={record.status_tone}
      editUrl={can.update ? `/companies/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/companies/${record.id}` : undefined}
      backUrl="/companies"
      facts={[
        { label: 'Type', value: record.type.replace('_', ' ') },
        { label: 'Email', value: record.email ?? '—' },
        { label: 'Phone', value: <span className="numeric">{record.phone ?? '—'}</span> },
        {
          label: 'Website',
          value: record.website ? (
            <a href={record.website} target="_blank" rel="noreferrer" className="text-accent-ink hover:underline">
              Open
            </a>
          ) : (
            '—'
          ),
        },
        { label: 'TRN', value: <span className="numeric">{record.trn ?? 'Restricted'}</span> },
        { label: 'Trade licence', value: record.trade_licence_no ?? '—' },
        {
          label: 'Licence expiry',
          value: (
            <span className="flex items-center justify-end gap-2">
              <DateText value={record.licence_expiry} />
              {record.licence_expiring && <StatusPill tone="warning">Soon</StatusPill>}
            </span>
          ),
        },
        { label: 'Payment terms', value: <span className="numeric">{record.payment_terms_days} days</span> },
        {
          label: 'Default commission',
          value: record.commission_rate_default ? <span className="numeric">{record.commission_rate_default}%</span> : '—',
        },
        { label: 'Location', value: [record.city, record.country].filter(Boolean).join(', ') || '—' },
      ]}
      timeline={timeline}
      aside={
        <Card>
          <CardHeader>
            <CardTitle>Contacts</CardTitle>
            <Num value={contacts.length} className="text-small text-ink-faint" />
          </CardHeader>
          {contacts.length === 0 ? (
            <EmptyState title="No contacts" description="Add the people you actually deal with at this company." />
          ) : (
            <ul className="divide-y divide-line">
              {contacts.map((contact) => (
                <li key={contact.id} className="px-5 py-3">
                  <p className="flex items-center gap-2 text-h3 text-ink">
                    {contact.name}
                    {contact.is_primary && <StatusPill tone="info">Primary</StatusPill>}
                  </p>
                  <p className="text-small text-ink-faint">{contact.position ?? '—'}</p>
                  <p className="text-small text-ink-soft">
                    {contact.email ?? ''} {contact.mobile ? `· ${contact.mobile}` : ''}
                  </p>
                </li>
              ))}
            </ul>
          )}
        </Card>
      }
    >
      <Card>
        <CardHeader>
          <CardTitle>Clients</CardTitle>
          <Link href={`/clients?company_id=${record.id}`} className="text-small text-accent-ink hover:underline">
            All clients
          </Link>
        </CardHeader>
        {clients.length === 0 ? (
          <EmptyState
            icon={<Users className="size-5" aria-hidden />}
            title="No clients linked"
            description="Individuals attached to this company appear here."
          />
        ) : (
          <ul className="divide-y divide-line">
            {clients.map((client) => (
              <li key={client.id}>
                <Link href={client.url} className="flex items-center justify-between gap-3 px-5 py-3 hover:bg-deck">
                  <span className="min-w-0">
                    <span className="block truncate text-h3 text-ink">{client.full_name}</span>
                    <span className="block text-small text-ink-faint">{client.reference}</span>
                  </span>
                  <StatusPill tone={client.status_tone}>{client.status.replace('_', ' ')}</StatusPill>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </DetailShell>
  )
}
