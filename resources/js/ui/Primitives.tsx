import type { HTMLAttributes, ReactNode } from 'react'
import { cn } from '@/lib/cn'
import { formatDate, formatDateTime, formatMoney, formatNumber, formatPercent, initials } from '@/lib/format'

/* ── surfaces ───────────────────────────────────────────────────────────── */

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('bg-hull border border-line rounded-card', className)} {...props} />
}

export function CardHeader({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('flex items-center justify-between gap-4 px-5 py-4 border-b border-line', className)} {...props} />
}

export function CardTitle({ className, ...props }: HTMLAttributes<HTMLHeadingElement>) {
  return <h2 className={cn('text-h2 text-ink', className)} {...props} />
}

export function CardBody({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('p-5', className)} {...props} />
}

export function Skeleton({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('animate-pulse rounded-pill bg-deck', className)} aria-hidden {...props} />
}

/* ── figures: the only sanctioned way to render a number ────────────────── */

export function Money({
  amount,
  currency = 'AED',
  compact = false,
  withCurrency = true,
  className,
}: {
  amount: string | number | null | undefined
  currency?: string
  compact?: boolean
  withCurrency?: boolean
  className?: string
}) {
  return (
    <span className={cn('numeric', className)}>
      {formatMoney(amount, currency, { compact, withCurrency })}
    </span>
  )
}

export function Num({
  value,
  fractionDigits = 0,
  className,
}: {
  value: number | string | null | undefined
  fractionDigits?: number
  className?: string
}) {
  return <span className={cn('numeric', className)}>{formatNumber(value, fractionDigits)}</span>
}

export function Percent({ value, className }: { value: number | string | null | undefined; className?: string }) {
  return <span className={cn('numeric', className)}>{formatPercent(value)}</span>
}

export function DateText({
  value,
  withTime = false,
  className,
}: {
  value: string | Date | null | undefined
  withTime?: boolean
  className?: string
}) {
  return (
    <span className={cn('numeric', className)}>
      {withTime ? formatDateTime(value) : formatDate(value)}
    </span>
  )
}

/* ── identity ───────────────────────────────────────────────────────────── */

export function Avatar({
  name,
  src,
  size = 'md',
  className,
}: {
  name: string | null | undefined
  src?: string | null
  size?: 'sm' | 'md' | 'lg'
  className?: string
}) {
  const dimensions = { sm: 'size-6 text-[10px]', md: 'size-8 text-micro', lg: 'size-10 text-h3' }[size]

  if (src) {
    return <img src={src} alt="" className={cn('rounded-full object-cover shrink-0', dimensions, className)} />
  }

  return (
    <span
      aria-hidden
      className={cn(
        'inline-grid place-items-center rounded-full shrink-0 bg-accent-soft text-accent-ink font-medium',
        dimensions,
        className,
      )}
    >
      {initials(name)}
    </span>
  )
}

export function IdentityCell({
  name,
  subtitle,
  src,
  href,
}: {
  name: string
  subtitle?: string | null
  src?: string | null
  href?: string
}) {
  const label = (
    <span className="min-w-0">
      <span className="block text-h3 text-ink truncate">{name}</span>
      {subtitle && <span className="block text-small text-ink-faint truncate">{subtitle}</span>}
    </span>
  )

  return (
    <span className="flex items-center gap-3 min-w-0">
      <Avatar name={name} src={src} />
      {href ? (
        <a href={href} className="min-w-0 hover:text-accent-ink">
          {label}
        </a>
      ) : (
        label
      )}
    </span>
  )
}

/* ── overflow chip: "ACE Homes LLC +2" ──────────────────────────────────── */

export function OverflowChips({ items, max = 1 }: { items: string[]; max?: number }) {
  if (items.length === 0) return <span className="text-ink-faint">—</span>
  const shown = items.slice(0, max)
  const rest = items.length - shown.length

  return (
    <span className="flex items-center gap-2 min-w-0">
      <span className="truncate">{shown.join(', ')}</span>
      {rest > 0 && (
        <span
          className="shrink-0 rounded-pill bg-deck px-2 py-px text-micro text-ink-soft"
          title={items.slice(max).join(', ')}
        >
          +{rest}
        </span>
      )}
    </span>
  )
}

/* ── empty / error states ───────────────────────────────────────────────── */

export function EmptyState({
  icon,
  title,
  description,
  action,
}: {
  icon?: ReactNode
  title: string
  description?: string
  action?: ReactNode
}) {
  return (
    <div className="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
      {icon && <span className="grid size-10 place-items-center rounded-card bg-deck text-ink-faint">{icon}</span>}
      <h3 className="text-h2 text-ink">{title}</h3>
      {description && <p className="max-w-prose text-body text-ink-soft">{description}</p>}
      {action && <div className="pt-2">{action}</div>}
    </div>
  )
}
