import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { RewardRow } from '@/pages/Crm/Rewards/Index'

export default function Show({
  record,
  can,
}: {
  record: RewardRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.client ?? 'Reward'}
      subtitle={record.code}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/crm/rewards/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/crm/rewards/${record.id}` : undefined}
      backUrl="/crm/rewards"
      facts={[
        { label: 'Type', value: record.type },
        { label: 'Value', value: record.value ? <Money amount={record.value} currency={record.currency} /> : '—' },
        { label: 'Points', value: record.points ?? '—' },
        { label: 'Code', value: <span className="numeric">{record.code ?? '—'}</span> },
        { label: 'Valid from', value: <DateText value={record.valid_from} /> },
        { label: 'Expires', value: <DateText value={record.expires_on} /> },
        { label: 'Redeemed', value: <DateText value={record.redeemed_at} withTime /> },
      ]}
    />
  )
}
