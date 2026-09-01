import { DetailShell } from '@/components/crud/DetailShell'
import { DateText } from '@/ui/Primitives'
import type { NdaRow } from '@/pages/Brokerage/Ndas/Index'

export default function Show({
  record,
  can,
}: {
  record: NdaRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.client ?? 'NDA'}
      subtitle={record.listing ?? 'Fleet-wide'}
      status={record.status?.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/ndas/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/ndas/${record.id}` : undefined}
      backUrl="/brokerage/ndas"
      facts={[
          { label: 'Scope', value: record.scope },
          { label: 'Signed', value: <DateText value={record.signed_at} /> },
          { label: 'Expires', value: <DateText value={record.expires_on} /> },
      ]}
    />
  )
}
