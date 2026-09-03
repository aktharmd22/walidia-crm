import { ResourceForm, type FormSection } from '@/components/crud/ResourceForm'

export interface Option {
  value: string | number
  label: string
}

export function sections(): FormSection[] {
  return [
    {
      title: 'The message',
      description: 'Merge fields go in double braces — {{client_name}}, {{yacht_name}}, {{charter_date}}, {{charter_time}}, {{reference}}.',
      fields: [
        { name: 'key', label: 'Key', required: true, help: 'How rules refer to this template. It does not change once rules use it.' },
        { name: 'name', label: 'Name', required: true },
        {
          name: 'channel',
          label: 'Channel',
          type: 'select',
          required: true,
          options: [
            { value: 'email', label: 'Email' },
            { value: 'whatsapp', label: 'WhatsApp' },
            { value: 'sms', label: 'SMS' },
            { value: 'in_app', label: 'In-app' },
          ],
        },
        {
          name: 'category',
          label: 'Audience',
          type: 'select',
          required: true,
          options: ['client', 'crew', 'vendor', 'internal'].map((value) => ({ value, label: value })),
        },
        { name: 'subject_en', label: 'Subject (English)', wide: true },
        { name: 'body_en', label: 'Body (English)', type: 'textarea', required: true, wide: true },
        { name: 'subject_ar', label: 'Subject (Arabic)', wide: true },
        { name: 'body_ar', label: 'Body (Arabic)', type: 'textarea', wide: true },
        { name: 'is_active', label: 'Active', type: 'checkbox', wide: true },
      ],
    },
  ]
}

export default function Create() {
  return (
    <ResourceForm
      title="Write a template"
      description="What the system says, in both languages, with the merge fields it expects."
      sections={sections()}
      initial={{
        key: '',
        name: '',
        channel: 'email',
        category: 'client',
        subject_en: '',
        body_en: '',
        subject_ar: '',
        body_ar: '',
        is_active: true,
      }}
      action="/engine/message-templates"
      submitLabel="Save"
      cancelUrl="/engine/message-templates"
    />
  )
}
