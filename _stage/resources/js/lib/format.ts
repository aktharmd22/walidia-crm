import { format, formatDistanceToNowStrict, parseISO } from 'date-fns'

/**
 * Formatting helpers. Money and dates are never hand-formatted in a component —
 * they go through here (or the <Money> / <DateText> primitives) so tabular
 * numerals and the Asia/Dubai display convention cannot be forgotten (D-010).
 */

export const APP_TIMEZONE = 'Asia/Dubai'

/** Money arrives from the server as a decimal string to avoid float drift (D-002). */
export function formatMoney(
  amount: string | number | null | undefined,
  currency = 'AED',
  options: { compact?: boolean; withCurrency?: boolean } = {},
): string {
  if (amount === null || amount === undefined || amount === '') return '—'

  const value = typeof amount === 'string' ? Number(amount) : amount
  if (Number.isNaN(value)) return '—'

  const { compact = false, withCurrency = true } = options

  const formatted = new Intl.NumberFormat('en-AE', {
    minimumFractionDigits: compact ? 0 : 2,
    maximumFractionDigits: compact ? 1 : 2,
    notation: compact ? 'compact' : 'standard',
  }).format(value)

  return withCurrency ? `${currency} ${formatted}` : formatted
}

export function formatNumber(value: number | string | null | undefined, fractionDigits = 0): string {
  if (value === null || value === undefined || value === '') return '—'
  const n = typeof value === 'string' ? Number(value) : value
  if (Number.isNaN(n)) return '—'
  return new Intl.NumberFormat('en-AE', {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
  }).format(n)
}

export function formatPercent(value: number | string | null | undefined, fractionDigits = 1): string {
  if (value === null || value === undefined || value === '') return '—'
  const n = typeof value === 'string' ? Number(value) : value
  if (Number.isNaN(n)) return '—'
  return `${formatNumber(n, fractionDigits)}%`
}

type DateInput = string | Date | null | undefined

function toDate(value: DateInput): Date | null {
  if (!value) return null
  const date = typeof value === 'string' ? parseISO(value) : value
  return Number.isNaN(date.getTime()) ? null : date
}

/** 20 Mar 2026 */
export function formatDate(value: DateInput): string {
  const date = toDate(value)
  return date ? format(date, 'dd MMM yyyy') : '—'
}

/** 20 Mar 2026, 14:30 */
export function formatDateTime(value: DateInput): string {
  const date = toDate(value)
  return date ? format(date, 'dd MMM yyyy, HH:mm') : '—'
}

/** 14:30 */
export function formatTime(value: DateInput): string {
  const date = toDate(value)
  return date ? format(date, 'HH:mm') : '—'
}

/** 3 days ago / in 2 hours */
export function formatRelative(value: DateInput): string {
  const date = toDate(value)
  if (!date) return '—'
  const suffix = date.getTime() < Date.now() ? 'ago' : 'from now'
  return `${formatDistanceToNowStrict(date)} ${suffix}`
}

export function initials(name: string | null | undefined): string {
  if (!name) return '—'
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('')
}
