import { ResourceForm, fv, type FormSection } from '@/components/crud/ResourceForm'
import type { DocumentRow } from '@/pages/Documents/Index'

const sections: FormSection[] = [
  {
    title: 'Document',
    fields: [
      { name: 'title', label: 'Title', required: true, wide: true },
      {
        name: 'category',
        label: 'Category',
        type: 'select',
        required: true,
        options: [
          { value: 'kyc', label: 'KYC' },
          { value: 'contract', label: 'Contract' },
          { value: 'certificate', label: 'Certificate' },
          { value: 'invoice', label: 'Invoice' },
          { value: 'proposal', label: 'Proposal' },
          { value: 'survey', label: 'Survey' },
          { value: 'statement', label: 'Statement' },
          { value: 'other', label: 'Other' },
        ],
      },
      {
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
          { value: 'active', label: 'Active' },
          { value: 'superseded', label: 'Superseded' },
          { value: 'expired', label: 'Expired' },
          { value: 'void', label: 'Void' },
        ],
      },
      { name: 'issued_on', label: 'Issued on', type: 'date' },
      { name: 'expires_on', label: 'Expires on', type: 'date' },
      {
        name: 'visibility',
        label: 'Visibility',
        type: 'select',
        required: true,
        options: [
          { value: 'internal', label: 'Internal only' },
          { value: 'client', label: 'Shareable with the client' },
          { value: 'owner', label: 'Owner portal' },
          { value: 'portal', label: 'Partner portal' },
        ],
      },
      { name: 'is_sensitive', label: 'Sensitive', type: 'checkbox' },
      { name: 'description', label: 'Description', type: 'textarea', wide: true },
    ],
  },
]

export default function DocumentEdit({ record }: { record: DocumentRow & { description: string | null } }) {
  return (
    <ResourceForm
      title={`Edit ${record.title}`}
      description="To replace the file itself, upload a new version from the document page — the previous file is kept."
      sections={sections}
      initial={{
        title: record.title,
        category: record.category,
        status: record.status,
        issued_on: fv(record.issued_on),
        expires_on: fv(record.expires_on),
        visibility: record.visibility,
        is_sensitive: record.is_sensitive,
        description: fv(record.description),
      }}
      action={`/documents/${record.id}`}
      method="put"
      submitLabel="Save changes"
      cancelUrl={`/documents/${record.id}`}
    />
  )
}
