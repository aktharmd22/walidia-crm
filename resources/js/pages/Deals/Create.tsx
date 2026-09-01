import { useMemo } from 'react'
import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

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

export function dealSections(pipelines: PipelineOption[], users: Option[], pipelineId?: number): FormSection[] {
  const stages = (pipelines.find((pipeline) => pipeline.id === Number(pipelineId)) ?? pipelines[0])?.stages ?? []

  return [
    {
      title: 'The deal',
      fields: [
        { name: 'title', label: 'Title', required: true, wide: true },
        {
          name: 'pipeline_id',
          label: 'Pipeline',
          type: 'select',
          required: true,
          options: pipelines.map((pipeline) => ({ value: pipeline.id, label: pipeline.name })),
        },
        {
          name: 'stage_id',
          label: 'Stage',
          type: 'select',
          required: true,
          options: stages.map((stage) => ({ value: stage.id, label: stage.name })),
        },
        { name: 'value', label: 'Value', type: 'money' },
        {
          name: 'currency',
          label: 'Currency',
          type: 'select',
          required: true,
          options: ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'OMR'].map((code) => ({ value: code, label: code })),
        },
        { name: 'expected_close_date', label: 'Expected close', type: 'date' },
        { name: 'assigned_user_id', label: 'Owner', type: 'select', options: users },
      ],
    },
  ]
}

export default function DealCreate({
  pipelines = [],
  users = [],
}: {
  pipelines?: PipelineOption[]
  users?: Option[]
}) {
  const first = pipelines[0]
  const sections = useMemo(() => dealSections(pipelines, users, first?.id), [pipelines, users, first?.id])

  return (
    <ResourceForm
      title="New deal"
      description="A deal is the board card. The record behind it keeps its own lifecycle."
      sections={sections}
      initial={{
        title: '',
        pipeline_id: first?.id ?? '',
        stage_id: first?.stages?.[0]?.id ?? '',
        client_id: '',
        company_id: '',
        yacht_id: '',
        value: '',
        currency: 'AED',
        expected_close_date: '',
        assigned_user_id: '',
      }}
      action="/deals"
      submitLabel="Open deal"
      cancelUrl="/deals"
    />
  )
}
