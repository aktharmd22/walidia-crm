import { cn } from '@/lib/cn'
import type { StatusTone } from '@/types'

const tones: Record<StatusTone, string> = {
  success: 'text-success bg-success-bg',
  info: 'text-info bg-info-bg',
  warning: 'text-warning bg-warning-bg',
  attention: 'text-attention bg-attention-bg',
  danger: 'text-danger bg-danger-bg',
  neutral: 'text-neutral bg-neutral-bg',
}

export interface StatusPillProps {
  tone?: StatusTone
  children: React.ReactNode
  className?: string
  /** Hide the leading dot only where the pill sits inside an already-coloured context. */
  dot?: boolean
}

/**
 * The status pill: coloured label on a tint of the same hue, with a leading dot.
 * Never a bare coloured dot with no label — a colour on its own means nothing
 * to a new member of staff.
 */
export function StatusPill({ tone = 'neutral', children, className, dot = true }: StatusPillProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center gap-2 rounded-pill px-2 py-px text-micro whitespace-nowrap',
        tones[tone],
        className,
      )}
    >
      {dot && <span className="size-[6px] rounded-full bg-current" aria-hidden />}
      {children}
    </span>
  )
}
