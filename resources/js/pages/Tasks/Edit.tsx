import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { taskSections } from '@/pages/Tasks/Create'

interface Option {
  value: string | number
  label: string
}

export default function TaskEdit({
  record,
  users = [],
}: {
  record: Record<string, unknown> & { id: number; title: string }
  users?: Option[]
}) {
  return (
    <ResourceForm
      title={`Edit task`}
      description={record.title}
      sections={taskSections(users, true)}
      initial={{
        title: fv(record.title),
        type: fv(record.type, 'next_action'),
        priority: fv(record.priority, 'normal'),
        assigned_user_id: (record.assignee as { id: number } | null)?.id ?? '',
        due_at: fv(record.due_at),
        description: fv(record.description),
        status: fv(record.status, 'open'),
      }}
      action={`/tasks/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/tasks/${record.id}`}
    />
  )
}
