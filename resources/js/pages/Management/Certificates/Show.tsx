import { DetailShell } from '@/components/crud/DetailShell'
import { DateText } from '@/ui/Primitives'
import type { CertificateRow } from '@/pages/Management/Certificates/Index'

export default function Show({
  record,
  can,
}: {
  record: CertificateRow
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.name}
      subtitle={record.yacht}
      status={record.status.replace(/_/g, ' ')}
      statusTone={record.status_tone}
      editUrl={can.update ? `/management/certificates/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/management/certificates/${record.id}` : undefined}
      backUrl="/management/certificates"
      facts={[
        { label: 'Type', value: record.type.replace(/_/g, ' ') },
        { label: 'Number', value: <span className="numeric">{record.number ?? '—'}</span> },
        { label: 'Issued by', value: record.issued_by ?? '—' },
        { label: 'Issued', value: <DateText value={record.issued_on} /> },
        { label: 'Expires', value: <DateText value={record.expires_on} /> },
        { label: 'Blocks charter', value: record.blocks_charter ? 'Yes' : 'No' },
      ]}
    />
  )
}
