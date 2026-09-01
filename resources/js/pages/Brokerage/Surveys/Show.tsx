import { DetailShell } from '@/components/crud/DetailShell'
import { DateText, Money } from '@/ui/Primitives'
import type { SurveyRow } from '@/pages/Brokerage/Surveys/Index'

export default function Show({
  record,
  can,
}: {
  record: SurveyRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.type.replace(/_/g, ' ')}
      subtitle={record.listing}
      status={record.status?.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/brokerage/surveys/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/brokerage/surveys/${record.id}` : undefined}
      backUrl="/brokerage/surveys"
      facts={[
          { label: 'Surveyor', value: record.surveyor_name ?? '—' },
          { label: 'Company', value: record.surveyor_company ?? '—' },
          { label: 'Scheduled', value: <DateText value={record.scheduled_at} withTime /> },
          { label: 'Cost', value: record.cost ? <Money amount={record.cost} /> : '—' },
          { label: 'Paid by', value: record.paid_by },
          { label: 'Remediation', value: record.remediation_estimate ? <Money amount={record.remediation_estimate} /> : '—' },
      ]}
    />
  )
}
