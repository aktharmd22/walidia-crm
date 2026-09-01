import { useEffect, useState } from 'react'
import { usePage } from '@inertiajs/react'
import { AlertTriangle, CheckCircle2, Info, X, XCircle } from 'lucide-react'
import { cn } from '@/lib/cn'
import type { SharedProps } from '@/types'

type ToastTone = 'success' | 'error' | 'warning' | 'info'

interface Toast {
  id: number
  tone: ToastTone
  message: string
}

const toneStyles: Record<ToastTone, { className: string; icon: typeof Info }> = {
  success: { className: 'border-success text-success', icon: CheckCircle2 },
  error: { className: 'border-danger text-danger', icon: XCircle },
  warning: { className: 'border-warning text-warning', icon: AlertTriangle },
  info: { className: 'border-info text-info', icon: Info },
}

let counter = 0

/**
 * Confirmations only. Validation errors belong on the field, never in a toast —
 * a toast that scrolls away is not an error message.
 */
export function Toaster() {
  const { props } = usePage<SharedProps>()
  const [toasts, setToasts] = useState<Toast[]>([])

  useEffect(() => {
    const incoming: Toast[] = []
    ;(['success', 'error', 'warning', 'info'] as const).forEach((tone) => {
      const message = props.flash?.[tone]
      if (message) incoming.push({ id: ++counter, tone, message })
    })

    if (incoming.length === 0) return

    setToasts((current) => [...current, ...incoming])
    const timer = window.setTimeout(() => {
      setToasts((current) => current.filter((toast) => !incoming.some((item) => item.id === toast.id)))
    }, 6000)

    return () => window.clearTimeout(timer)
  }, [props.flash])

  if (toasts.length === 0) return null

  return (
    <div className="fixed bottom-4 end-4 z-toast flex w-[min(420px,calc(100vw-32px))] flex-col gap-2" role="status" aria-live="polite">
      {toasts.map((toast) => {
        const { className, icon: Icon } = toneStyles[toast.tone]
        return (
          <div
            key={toast.id}
            className={cn(
              'flex items-start gap-3 rounded-card border-s-2 border border-line bg-hull p-4 shadow-toast',
              className,
            )}
          >
            <Icon className="mt-px size-4 shrink-0" aria-hidden />
            <p className="flex-1 text-body text-ink">{toast.message}</p>
            <button
              type="button"
              onClick={() => setToasts((current) => current.filter((item) => item.id !== toast.id))}
              className="rounded-pill p-1 text-ink-faint hover:bg-deck hover:text-ink"
              aria-label="Dismiss"
            >
              <X className="size-4" aria-hidden />
            </button>
          </div>
        )
      })}
    </div>
  )
}
