import type { PageProps as InertiaPageProps } from '@inertiajs/core'

export type StatusTone = 'success' | 'info' | 'warning' | 'attention' | 'danger' | 'neutral'

export interface AuthUser {
  id: number
  name: string
  email: string
  avatar_url: string | null
  locale: 'en' | 'ar'
  roles: string[]
  permissions: string[]
  two_factor_enabled: boolean
}

export interface FlashMessages {
  success?: string | null
  error?: string | null
  warning?: string | null
  info?: string | null
}

export interface NavChild {
  key: string
  label: string
  href: string
  badge?: number | null
}

export interface NavSection {
  key: string
  label: string
  icon: string
  href: string | null
  children: NavChild[]
}

export interface SharedProps extends InertiaPageProps {
  auth: { user: AuthUser | null }
  nav: NavSection[]
  flash: FlashMessages
  chrome: { theme: 'navy' | 'light'; accent: 'brass' | 'blue' }
  locale: 'en' | 'ar'
  direction: 'ltr' | 'rtl'
  app: { name: string; env: string; currency: string; timezone: string }
  [key: string]: unknown
}

/** Laravel paginator shape, as returned by an API Resource collection. */
export interface Paginated<T> {
  data: T[]
  links: { url: string | null; label: string; active: boolean }[]
  meta: {
    current_page: number
    from: number | null
    last_page: number
    per_page: number
    to: number | null
    total: number
  }
}

/** A single failed gate condition, as returned by the gate evaluator (D-004). */
export interface GateFailure {
  rule: string
  condition: string
  message: string
  resolution: { label: string; href: string } | null
}

export interface GateResult {
  verdict: 'pass' | 'warn' | 'block'
  failures: GateFailure[]
  overridable: boolean
}
