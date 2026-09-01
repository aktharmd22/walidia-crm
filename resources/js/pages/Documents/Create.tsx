import { Head, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { UploadCloud } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle } from '@/ui/Primitives'
import { Checkbox, Field, Input, Select, Textarea } from '@/ui/Field'

/**
 * Upload is its own page rather than a generic form: it is the one place a
 * file leaves someone's laptop and becomes a record, so the constraints —
 * type, size, visibility, expiry — are stated in front of them.
 */
export default function DocumentCreate({
  subject,
}: {
  subject?: { type: string; id: number; label: string }
}) {
  const form = useForm<{
    file: File | null
    title: string
    description: string
    category: string
    subject_type: string
    subject_id: string
    issued_on: string
    expires_on: string
    visibility: string
    is_sensitive: boolean
    requires_signature: boolean
  }>({
    file: null,
    title: '',
    description: '',
    category: 'other',
    subject_type: subject?.type ?? '',
    subject_id: subject ? String(subject.id) : '',
    issued_on: '',
    expires_on: '',
    visibility: 'internal',
    is_sensitive: false,
    requires_signature: false,
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    form.post('/documents', { forceFormData: true })
  }

  return (
    <>
      <Head title="Upload document" />

      <PageHeader
        title="Upload a document"
        description={subject ? `Attaching to ${subject.label}` : 'Files are stored privately and never linked publicly.'}
      />

      <form onSubmit={submit} className="grid gap-5 xl:grid-cols-[1fr_320px]">
        <div className="flex flex-col gap-5">
          <Card>
            <CardHeader>
              <CardTitle>The file</CardTitle>
            </CardHeader>
            <CardBody className="flex flex-col gap-3">
              <Field
                label="File"
                required
                error={form.errors.file}
                help="PDF, image, Office document or ZIP. Up to 25 MB."
              >
                <label className="flex cursor-pointer items-center gap-3 rounded-card border border-dashed border-line bg-deck px-4 py-6 hover:border-line-strong">
                  <UploadCloud className="size-5 text-ink-faint" aria-hidden />
                  <span className="text-body text-ink-soft">
                    {form.data.file ? form.data.file.name : 'Choose a file…'}
                  </span>
                  <input
                    type="file"
                    className="sr-only"
                    onChange={(event) => {
                      const file = event.target.files?.[0] ?? null
                      form.setData('file', file)
                      if (file && !form.data.title) form.setData('title', file.name.replace(/\.[^.]+$/, ''))
                    }}
                  />
                </label>
              </Field>

              <Input
                label="Title"
                required
                value={form.data.title}
                error={form.errors.title}
                onChange={(event) => form.setData('title', event.target.value)}
              />

              <Select
                label="Category"
                required
                value={form.data.category}
                error={form.errors.category}
                onChange={(event) => form.setData('category', event.target.value)}
                options={[
                  { value: 'kyc', label: 'KYC' },
                  { value: 'contract', label: 'Contract' },
                  { value: 'certificate', label: 'Certificate' },
                  { value: 'invoice', label: 'Invoice' },
                  { value: 'proposal', label: 'Proposal' },
                  { value: 'survey', label: 'Survey' },
                  { value: 'statement', label: 'Statement' },
                  { value: 'other', label: 'Other' },
                ]}
              />

              <Textarea
                label="Description"
                value={form.data.description}
                error={form.errors.description}
                onChange={(event) => form.setData('description', event.target.value)}
              />
            </CardBody>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Dates and handling</CardTitle>
            </CardHeader>
            <CardBody className="grid gap-3 md:grid-cols-2">
              <Input
                label="Issued on"
                type="date"
                value={form.data.issued_on}
                error={form.errors.issued_on}
                onChange={(event) => form.setData('issued_on', event.target.value)}
              />
              <Input
                label="Expires on"
                type="date"
                value={form.data.expires_on}
                error={form.errors.expires_on}
                help="Expiry drives the reminder and the certificate gates."
                onChange={(event) => form.setData('expires_on', event.target.value)}
              />
              <Select
                label="Visibility"
                required
                value={form.data.visibility}
                error={form.errors.visibility}
                onChange={(event) => form.setData('visibility', event.target.value)}
                options={[
                  { value: 'internal', label: 'Internal only' },
                  { value: 'client', label: 'Shareable with the client' },
                  { value: 'owner', label: 'Owner portal' },
                  { value: 'portal', label: 'Partner portal' },
                ]}
              />
              <div className="flex flex-col justify-end gap-3">
                <Checkbox
                  label="Sensitive"
                  description="Restricts access to users with VIP permission."
                  checked={form.data.is_sensitive}
                  onChange={(event) => form.setData('is_sensitive', event.target.checked)}
                />
                <Checkbox
                  label="Needs a signature"
                  checked={form.data.requires_signature}
                  onChange={(event) => form.setData('requires_signature', event.target.checked)}
                />
              </div>
            </CardBody>
          </Card>
        </div>

        <Card>
          <CardBody className="flex flex-col gap-3">
            <Button type="submit" variant="primary" block loading={form.processing}>
              Upload document
            </Button>
            <p className="text-small text-ink-faint">
              The file is stored under a random name on a private disk. Nobody can reach it without a policy check, and
              every download is logged.
            </p>
          </CardBody>
        </Card>
      </form>
    </>
  )
}
