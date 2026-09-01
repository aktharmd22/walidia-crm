import { ResourceForm, fv } from '@/components/crud/ResourceForm'
import { dealSections } from '@/pages/Deals/Create'

interface Option {
  value: string | number
  label: string
}

interface PipelineOption {
  id: number
  key: string
  name: string
  stages: { id: number; name: string; key: string }[]
}

export default function DealEdit({
  record,
  pipelines = [],
  users = [],
}: {
  record: Record<string, unknown> & { id: number; title: string }
  pipelines?: PipelineOption[]
  users?: Option[]
}) {
  const pipelineId = (record.pipeline as { id: number } | null)?.id

  return (
    <ResourceForm
      title={`Edit ${record.title}`}
      sections={dealSections(pipelines, users, pipelineId)}
      initial={{
        title: fv(record.title),
        pipeline_id: pipelineId ?? '',
        stage_id: (record.stage as { id: number } | null)?.id ?? '',
        client_id: (record.client as { id: number } | null)?.id ?? '',
        company_id: '',
        yacht_id: (record.yacht as { id: number } | null)?.id ?? '',
        value: fv(record.value),
        currency: fv(record.currency, 'AED'),
        expected_close_date: fv(record.expected_close_date),
        assigned_user_id: (record.assignee as { id: number } | null)?.id ?? '',
      }}
      action={`/deals/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/deals/${record.id}`}
    />
  )
}
