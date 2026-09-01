import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function taskSections(users: Option[], withStatus = false): FormSection[] {
  return [
    {
      title: 'The task',
      fields: [
        { name: 'title', label: 'Title', required: true, wide: true },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: [
            { value: 'next_action', label: 'Next action' },
            { value: 'follow_up', label: 'Follow-up' },
            { value: 'approval', label: 'Approval' },
            { value: 'ops', label: 'Operations' },
            { value: 'compliance', label: 'Compliance' },
          ],
        },
        {
          name: 'priority',
          label: 'Priority',
          type: 'select',
          required: true,
          options: [
            { value: 'low', label: 'Low' },
            { value: 'normal', label: 'Normal' },
            { value: 'high', label: 'High' },
            { value: 'urgent', label: 'Urgent' },
          ],
        },
        { name: 'assigned_user_id', label: 'Assign to', type: 'select', options: users },
        { name: 'due_at', label: 'Due', type: 'datetime' },
        { name: 'description', label: 'Detail', type: 'textarea', wide: true },
        ...(withStatus
          ? [
              {
                name: 'status',
                label: 'Status',
                type: 'select' as const,
                required: true,
                options: [
                  { value: 'open', label: 'Open' },
                  { value: 'done', label: 'Done' },
                  { value: 'cancelled', label: 'Cancelled' },
                ],
              },
            ]
          : []),
      ],
    },
  ]
}

export default function TaskCreate({ users = [] }: { users?: Option[] }) {
  return (
    <ResourceForm
      title="New task"
      sections={taskSections(users)}
      initial={{
        title: '',
        type: 'next_action',
        priority: 'normal',
        assigned_user_id: '',
        due_at: '',
        description: '',
        subject_type: '',
        subject_id: '',
      }}
      action="/tasks"
      submitLabel="Create task"
      cancelUrl="/tasks"
    />
  )
}
