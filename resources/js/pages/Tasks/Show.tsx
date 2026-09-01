import { router } from '@inertiajs/react'
import { Check, RotateCcw } from 'lucide-react'
import { DetailShell } from '@/components/crud/DetailShell'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, DateText } from '@/ui/Primitives'
import type { TaskRow } from '@/pages/Tasks/Index'

export default function TaskShow({
  record,
  can,
}: {
  record: TaskRow & { description: string | null; completed_at: string | null; created_at: string | null }
  can: { update?: boolean; delete?: boolean }
}) {
  return (
    <DetailShell
      title={record.title}
      subtitle={record.subject?.label ?? record.reference}
      status={record.is_overdue ? 'Overdue' : record.status}
      statusTone={record.status_tone}
      editUrl={can.update ? `/tasks/${record.id}/edit` : undefined}
      archiveUrl={can.delete ? `/tasks/${record.id}` : undefined}
      backUrl="/tasks"
      actions={[
        record.status === 'open' ? (
          <Button
            key="done"
            variant="primary"
            icon={<Check className="size-4" />}
            onClick={() => router.post(`/tasks/${record.id}/complete`)}
          >
            Mark done
          </Button>
        ) : (
          <Button
            key="reopen"
            variant="secondary"
            icon={<RotateCcw className="size-4" />}
            onClick={() => router.post(`/tasks/${record.id}/reopen`)}
          >
            Reopen
          </Button>
        ),
      ]}
      facts={[
        { label: 'Type', value: record.type.replace('_', ' ') },
        { label: 'Priority', value: record.priority },
        { label: 'Owner', value: record.assignee?.name ?? 'Unassigned' },
        { label: 'Due', value: <DateText value={record.due_at} withTime /> },
        { label: 'Completed', value: <DateText value={record.completed_at} withTime /> },
        { label: 'Raised by', value: record.source },
        { label: 'Created', value: <DateText value={record.created_at} /> },
      ]}
    >
      {record.description && (
        <Card>
          <CardHeader>
            <CardTitle>Detail</CardTitle>
          </CardHeader>
          <CardBody>
            <p className="whitespace-pre-line text-body text-ink-soft">{record.description}</p>
          </CardBody>
        </Card>
      )}
    </DetailShell>
  )
}
