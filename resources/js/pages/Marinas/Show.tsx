import { DetailShell } from '@/components/crud/DetailShell'
import { StatusPill } from '@/ui/StatusPill'
import type { MarinaRow } from '@/pages/Marinas/Index'

export default function MarinaShow({
  record,
  can,
}: {
  record: MarinaRow & {
    name_ar: string | null
    emirate: string | null
    latitude: string | null
    longitude: string | null
    contact_name: string | null
    contact_phone: string | null
    contact_email: string | null
    manifest_format: string | null
  }
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.name}
      subtitle={[record.city, record.country].filter(Boolean).join(', ')}
      status={record.is_active ? 'Active' : 'Inactive'}
      statusTone={record.status_tone}
      editUrl={can.update ? `/fleet/marinas/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/fleet/marinas/${record.id}` : undefined}
      backUrl="/fleet/marinas"
      facts={[
        { label: 'Timezone', value: <span className="numeric">{record.timezone}</span> },
        { label: 'Emirate / region', value: record.emirate ?? '—' },
        {
          label: 'Coordinates',
          value:
            record.latitude && record.longitude ? (
              <span className="numeric">
                {record.latitude}, {record.longitude}
              </span>
            ) : (
              '—'
            ),
        },
        { label: 'Contact', value: record.contact_name ?? '—' },
        { label: 'Phone', value: <span className="numeric">{record.contact_phone ?? '—'}</span> },
        { label: 'Email', value: record.contact_email ?? '—' },
        {
          label: 'Manifest',
          value: record.requires_manifest ? (
            <StatusPill tone="warning">{record.manifest_format?.toUpperCase() ?? 'Required'}</StatusPill>
          ) : (
            'Not required'
          ),
        },
      ]}
    />
  )
}
