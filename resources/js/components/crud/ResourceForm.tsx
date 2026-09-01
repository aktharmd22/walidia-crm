import type { ReactNode } from 'react'
import { Head, Link, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle } from '@/ui/Primitives'
import { Checkbox, Input, Select, Textarea } from '@/ui/Field'

export type FieldType = 'text' | 'email' | 'tel' | 'number' | 'money' | 'date' | 'datetime' | 'textarea' | 'select' | 'checkbox' | 'multiselect'

export interface FormField {
  name: string
  label: string
  type?: FieldType
  required?: boolean
  help?: string
  placeholder?: string
  options?: { value: string | number; label: string }[]
  /** Full width in the two-column grid. */
  wide?: boolean
  hidden?: boolean
}

export interface FormSection {
  title: string
  description?: string
  fields: FormField[]
}

/** What a form field can hold. Matches what Inertia can serialise. */
export type FormValue = string | number | boolean | null | undefined | File | (string | number)[]

export type FormValues = Record<string, FormValue>

/**
 * Coerces a value out of a server payload into something a form field can
 * hold. Edit screens receive `unknown` from Inertia; this is where that stops.
 */
export function fv(value: unknown, fallback: FormValue = ''): FormValue {
  if (value === null || value === undefined) return fallback
  if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') return value
  if (Array.isArray(value)) {
    return value.filter((item): item is string | number => typeof item === 'string' || typeof item === 'number')
  }
  return fallback
}

export interface ResourceFormProps {
  title: string
  description?: string
  sections: FormSection[]
  initial: FormValues
  action: string
  method?: 'post' | 'put'
  submitLabel?: string
  cancelUrl: string
  aside?: ReactNode
}

/**
 * The create/edit form, once.
 *
 * Field definitions come from the page, validation comes from the server, and
 * errors land on the field they belong to — never in a toast that scrolls away.
 */
export function ResourceForm({
  title,
  description,
  sections,
  initial,
  action,
  method = 'post',
  submitLabel = 'Save',
  cancelUrl,
  aside,
}: ResourceFormProps) {
  const form = useForm<FormValues>(initial)

  function submit(event: FormEvent) {
    event.preventDefault()
    if (method === 'put') {
      form.put(action, { preserveScroll: true })
    } else {
      form.post(action, { preserveScroll: true })
    }
  }

  function render(field: FormField) {
    if (field.hidden) return null

    const value = form.data[field.name]
    const error = form.errors[field.name] as string | undefined
    const set = (next: FormValue) => form.setData(field.name, next)

    switch (field.type) {
      case 'textarea':
        return (
          <Textarea
            key={field.name}
            label={field.label}
            required={field.required}
            help={field.help}
            placeholder={field.placeholder}
            error={error}
            value={(value as string) ?? ''}
            onChange={(event) => set(event.target.value)}
          />
        )

      case 'select':
        return (
          <Select
            key={field.name}
            label={field.label}
            required={field.required}
            help={field.help}
            error={error}
            placeholder={field.placeholder ?? 'Select…'}
            options={field.options ?? []}
            value={(value as string) ?? ''}
            onChange={(event) => set(event.target.value)}
          />
        )

      case 'checkbox':
        return (
          <div key={field.name} className="flex items-end pb-2">
            <Checkbox
              label={field.label}
              description={field.help}
              checked={Boolean(value)}
              onChange={(event) => set(event.target.checked)}
            />
          </div>
        )

      case 'multiselect':
        return (
          <div key={field.name} className="flex flex-col gap-2">
            <span className="text-h3 text-ink">
              {field.label}
              {field.required && <span className="text-danger ms-1">*</span>}
            </span>
            <div className="flex flex-wrap gap-3 rounded-card border border-line bg-hull p-3">
              {(field.options ?? []).map((option) => {
                const list = Array.isArray(value) ? (value as (string | number)[]) : []
                const checked = list.includes(option.value)

                return (
                  <Checkbox
                    key={String(option.value)}
                    label={option.label}
                    checked={checked}
                    onChange={(event) =>
                      set(
                        event.target.checked
                          ? [...list, option.value]
                          : list.filter((item) => item !== option.value),
                      )
                    }
                  />
                )
              })}
            </div>
            {error && <p className="text-small text-danger">{error}</p>}
            {!error && field.help && <p className="text-small text-ink-faint">{field.help}</p>}
          </div>
        )

      default:
        return (
          <Input
            key={field.name}
            label={field.label}
            type={field.type === 'money' || field.type === 'number' ? 'number' : (field.type ?? 'text')}
            step={field.type === 'money' ? '0.01' : undefined}
            numeric={field.type === 'money' || field.type === 'number'}
            required={field.required}
            help={field.help}
            placeholder={field.placeholder}
            error={error}
            value={(value as string) ?? ''}
            onChange={(event) => set(event.target.value)}
          />
        )
    }
  }

  return (
    <>
      <Head title={title} />

      <PageHeader title={title} description={description} />

      <form onSubmit={submit} className="grid gap-5 xl:grid-cols-[1fr_320px]">
        <div className="flex flex-col gap-5">
          {sections.map((section) => (
            <Card key={section.title}>
              <CardHeader>
                <div>
                  <CardTitle>{section.title}</CardTitle>
                  {section.description && <p className="mt-1 text-small text-ink-faint">{section.description}</p>}
                </div>
              </CardHeader>
              <CardBody>
                <div className="grid gap-3 md:grid-cols-2">
                  {section.fields.map((field) => (
                    <div key={field.name} className={field.wide ? 'md:col-span-2' : undefined}>
                      {render(field)}
                    </div>
                  ))}
                </div>
              </CardBody>
            </Card>
          ))}
        </div>

        <div className="flex flex-col gap-5">
          {aside}
          <Card>
            <CardBody className="flex flex-col gap-3">
              <Button type="submit" variant="primary" block loading={form.processing}>
                {submitLabel}
              </Button>
              <Link href={cancelUrl}>
                <Button variant="ghost" block type="button">
                  Cancel
                </Button>
              </Link>
              {form.hasErrors && (
                <p className="text-small text-danger">
                  Some fields need attention — they are marked above.
                </p>
              )}
            </CardBody>
          </Card>
        </div>
      </form>
    </>
  )
}
