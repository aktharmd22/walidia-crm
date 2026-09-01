import { forwardRef } from 'react'
import type { ButtonHTMLAttributes, ReactNode } from 'react'
import { Loader2 } from 'lucide-react'
import { cn } from '@/lib/cn'

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'destructive' | 'link'
export type ButtonSize = 'sm' | 'md' | 'lg'

const variants: Record<ButtonVariant, string> = {
  primary:
    'bg-accent text-accent-on border border-accent hover:bg-accent-hover active:bg-accent-press disabled:bg-neutral disabled:border-neutral',
  secondary:
    'bg-hull text-ink border border-line hover:bg-deck active:bg-deck disabled:text-ink-faint',
  ghost:
    'bg-transparent text-ink-soft border border-transparent hover:bg-deck hover:text-ink disabled:text-ink-faint',
  destructive:
    'bg-danger text-white border border-danger hover:opacity-90 active:opacity-80 disabled:opacity-50',
  link: 'bg-transparent text-accent border border-transparent underline-offset-4 hover:underline p-0 h-auto',
}

const sizes: Record<ButtonSize, string> = {
  sm: 'h-8 px-3 text-small gap-2',
  md: 'h-field px-4 text-body gap-2',
  lg: 'h-10 px-5 text-body gap-2 md:h-field',
}

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant
  size?: ButtonSize
  loading?: boolean
  icon?: ReactNode
  iconEnd?: ReactNode
  block?: boolean
}

/**
 * The one button. `loading` disables and swaps in a spinner without changing
 * width, so a row of buttons never reflows mid-submit.
 */
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button(
  { variant = 'secondary', size = 'md', loading = false, icon, iconEnd, block, className, children, disabled, type = 'button', ...props },
  ref,
) {
  return (
    <button
      ref={ref}
      type={type}
      disabled={disabled || loading}
      aria-busy={loading || undefined}
      className={cn(
        'inline-flex items-center justify-center rounded-pill font-medium whitespace-nowrap',
        'transition-colors duration-fast ease-std',
        'disabled:cursor-not-allowed',
        variants[variant],
        variant !== 'link' && sizes[size],
        block && 'w-full',
        className,
      )}
      {...props}
    >
      {loading ? <Loader2 className="size-4 animate-spin" aria-hidden /> : icon}
      {children}
      {!loading && iconEnd}
    </button>
  )
})
