import { DetailShell } from '@/components/crud/DetailShell'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import type { CommunicationRow } from '@/pages/Automation/Communications/Index'

/** One message, exactly as it went out. */
export default function CommunicationShow({ record }: { record: CommunicationRow }) {
  return (
    <DetailShell
      title={record.subject ?? `${record.channel} message`}
      subtitle={record.client ?? record.to_address}
      status={record.status}
      statusTone={record.status_tone}
      backUrl="/engine/communications"
      facts={[
        { label: 'Channel', value: record.channel },
        { label: 'To', value: record.to_address ?? '—' },
        { label: 'Sent', value: <DateText value={record.sent_at} withTime /> },
        { label: 'Delivered', value: <DateText value={record.delivered_at} withTime /> },
        { label: 'Read', value: <DateText value={record.read_at} withTime /> },
      ]}
    >
      {record.failure_reason && (
        <p className="rounded-card border border-danger bg-danger-bg px-4 py-3 text-small text-danger">
          {record.failure_reason}
        </p>
      )}

      <Card>
        <CardHeader>
          <CardTitle>What was sent</CardTitle>
        </CardHeader>
        <CardBody>
          <p className="whitespace-pre-wrap text-body text-ink">{record.body}</p>
        </CardBody>
      </Card>
    </DetailShell>
  )
}
