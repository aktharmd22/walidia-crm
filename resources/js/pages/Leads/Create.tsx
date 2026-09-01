import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

interface Option {
  value: string | number
  label: string
}

export function leadSections(sources: Option[], users: Option[], withStatus = false): FormSection[] {
  return [
    {
      title: 'The enquiry',
      fields: [
        { name: 'name', label: 'Name', required: true },
        {
          name: 'business_line',
          label: 'Business line',
          type: 'select',
          required: true,
          options: [
            { value: 'charter', label: 'Charter' },
            { value: 'brokerage', label: 'Brokerage' },
            { value: 'management', label: 'Management' },
          ],
        },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'mobile', label: 'Mobile', type: 'tel', placeholder: '+971 50 123 4567' },
        { name: 'source_id', label: 'Source', type: 'select', options: sources },
        { name: 'assigned_user_id', label: 'Assign to', type: 'select', options: users },
        { name: 'message', label: 'What they asked for', type: 'textarea', wide: true },
        ...(withStatus
          ? [
              {
                name: 'status',
                label: 'Status',
                type: 'select' as const,
                required: true,
                options: [
                  { value: 'new', label: 'New' },
                  { value: 'contacted', label: 'Contacted' },
                  { value: 'qualified', label: 'Qualified' },
                  { value: 'registered', label: 'Registered' },
                  { value: 'unqualified', label: 'Unqualified' },
                  { value: 'duplicate', label: 'Duplicate' },
                ],
              },
              { name: 'next_follow_up_at', label: 'Next follow-up', type: 'datetime' as const },
            ]
          : []),
      ],
    },
  ]
}

export default function LeadCreate({ sources = [], users = [] }: { sources?: Option[]; users?: Option[] }) {
  return (
    <ResourceForm
      title="New lead"
      description="The response clock starts as soon as this is saved."
      sections={leadSections(sources, users)}
      initial={{
        name: '',
        business_line: 'charter',
        email: '',
        mobile: '',
        source_id: '',
        assigned_user_id: '',
        message: '',
      }}
      action="/leads"
      submitLabel="Create lead"
      cancelUrl="/leads"
    />
  )
}
