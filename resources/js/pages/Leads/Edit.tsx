import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { leadSections } from '@/pages/Leads/Create'

interface Option {
  value: string | number
  label: string
}

export default function LeadEdit({
  record,
  sources = [],
  users = [],
}: {
  record: Record<string, unknown> & { id: number; name: string }
  sources?: Option[]
  users?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit ${record.name}`}
      description="Changes are audited: who changed what, from what, and when."
      sections={leadSections(sources, users, true)}
      initial={{
        name: fv(record.name),
        business_line: fv(record.business_line, 'charter'),
        email: fv(record.email),
        mobile: fv(record.mobile),
        source_id: fv(record.source_id),
        assigned_user_id: (record.assignee as { id: number } | null)?.id ?? '',
        message: fv(record.message),
        status: fv(record.status, 'new'),
        next_follow_up_at: fv(record.next_follow_up_at),
      }}
      action={`/leads/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/leads/${record.id}`}
    />
  )
}
