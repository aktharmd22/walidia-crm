import { forwardRef, useId } from 'react'
import type { InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes, ReactNode } from 'react'
import { cn } from '@/lib/cn'

const control =
  'w-full rounded-card border border-line bg-hull px-3 text-body text-ink placeholder:text-ink-faint ' +
  'transition-colors duration-fast ease-std hover:border-line-strong ' +
  'disabled:bg-deck disabled:text-ink-faint disabled:cursor-not-allowed ' +
  'aria-[invalid=true]:border-danger'

export function Label({
  htmlFor,
  children,
  required,
  className,
}: {
  htmlFor?: string
  children: ReactNode
  required?: boolean
  className?: string
}) {
  return (
    <label htmlFor={htmlFor} className={cn('block text-h3 text-ink', className)}>
      {children}
      {required && (
        <span className="text-danger ms-1" aria-hidden>
          *
        </span>
      )}
    </label>
  )
}

export function FieldError({ children }: { children?: ReactNode }) {
  if (!children) return null
  return (
    <p className="text-small text-danger" role="alert">
      {children}
    </p>
  )
}

export function HelpText({ children }: { children?: ReactNode }) {
  if (!children) return null
  return <p className="text-small text-ink-faint">{children}</p>
}

export interface FieldProps {
  label?: string
  error?: string
  help?: string
  required?: boolean
  htmlFor?: string
  children: ReactNode
  className?: string
}

/** Label + control + help/error, on the 12px vertical rhythm. */
export function Field({ label, error, help, required, htmlFor, children, className }: FieldProps) {
  return (
    <div className={cn('flex flex-col gap-2', className)}>
      {label && (
        <Label htmlFor={htmlFor} required={required}>
          {label}
        </Label>
      )}
      {children}
      <FieldError>{error}</FieldError>
      {!error && <HelpText>{help}</HelpText>}
    </div>
  )
}

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string
  error?: string
  help?: string
  numeric?: boolean
}

export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(
  { label, error, help, numeric, className, required, id, ...props },
  ref,
) {
  const generated = useId()
  const inputId = id ?? generated

  const field = (
    <input
      ref={ref}
      id={inputId}
      required={required}
      aria-invalid={error ? true : undefined}
      className={cn(control, 'h-field', numeric && 'numeric text-end', className)}
      {...props}
    />
  )

  if (!label && !error && !help) return field

  return (
    <Field label={label} error={error} help={help} required={required} htmlFor={inputId}>
      {field}
    </Field>
  )
})

export interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string
  error?: string
  help?: string
}

export const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(function Textarea(
  { label, error, help, className, required, id, rows = 4, ...props },
  ref,
) {
  const generated = useId()
  const inputId = id ?? generated

  return (
    <Field label={label} error={error} help={help} required={required} htmlFor={inputId}>
      <textarea
        ref={ref}
        id={inputId}
        rows={rows}
        required={required}
        aria-invalid={error ? true : undefined}
        className={cn(control, 'py-2 leading-body resize-y', className)}
        {...props}
      />
    </Field>
  )
})

export interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  label?: string
  error?: string
  help?: string
  options: { value: string | number; label: string }[]
  placeholder?: string
}

export const Select = forwardRef<HTMLSelectElement, SelectProps>(function Select(
  { label, error, help, options, placeholder, className, required, id, ...props },
  ref,
) {
  const generated = useId()
  const inputId = id ?? generated

  const field = (
    <select
      ref={ref}
      id={inputId}
      required={required}
      aria-invalid={error ? true : undefined}
      className={cn(control, 'h-field pe-8', className)}
      {...props}
    >
      {placeholder && <option value="">{placeholder}</option>}
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  )

  if (!label && !error && !help) return field

  return (
    <Field label={label} error={error} help={help} required={required} htmlFor={inputId}>
      {field}
    </Field>
  )
})

export function Checkbox({
  label,
  description,
  className,
  id,
  ...props
}: InputHTMLAttributes<HTMLInputElement> & { label: string; description?: string }) {
  const generated = useId()
  const inputId = id ?? generated

  return (
    <div className={cn('flex items-start gap-3', className)}>
      <input
        type="checkbox"
        id={inputId}
        className="form-checkbox mt-px size-4 rounded-[3px] border-line text-accent focus:ring-0"
        {...props}
      />
      <span className="min-w-0">
        <label htmlFor={inputId} className="block text-body text-ink cursor-pointer">
          {label}
        </label>
        {description && <span className="block text-small text-ink-faint">{description}</span>}
      </span>
    </div>
  )
}
