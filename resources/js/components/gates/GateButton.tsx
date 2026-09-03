import { useState, type ReactNode } from 'react'
import { Link, router, useForm } from '@inertiajs/react'
import { AlertTriangle, ShieldAlert, ShieldCheck } from 'lucide-react'
import { Button, type ButtonProps } from '@/ui/Button'
import { Modal, Tooltip } from '@/ui/Overlays'
import { Textarea } from '@/ui/Field'
import { cn } from '@/lib/cn'
import type { GateResult } from '@/types'

/**
 * A guarded action, with its reasoning attached.
 *
 * A blocked button is never a mystery and never a silent failure: it is
 * disabled, it says what is missing, and each missing thing links to the screen
 * that fixes it. Admins get an override path — with a reason that goes on the
 * record permanently.
 */
export function GateButton({
  gate,
  action,
  label,
  canOverride = false,
  variant = 'primary',
  icon,
  confirm,
  ...props
}: {
  gate: GateResult
  /** POST target for the transition. */
  action: string
  label: string
  canOverride?: boolean
  confirm?: string
  icon?: ReactNode
} & Omit<ButtonProps, 'children' | 'onClick'>) {
  const [overrideOpen, setOverrideOpen] = useState(false)
  const override = useForm({ override_reason: '' })
  const blocked = gate.verdict === 'block'

  function run() {
    if (confirm && !window.confirm(confirm)) return
    router.post(action, {}, { preserveScroll: true })
  }

  return (
    <>
      <div className="flex flex-col gap-2">
        <div className="flex items-center gap-2">
          <Tooltip content={blocked ? gate.failures[0]?.message : undefined}>
            <span>
              <Button
                {...props}
                variant={blocked ? 'secondary' : variant}
                icon={icon}
                disabled={blocked}
                onClick={run}
              >
                {label}
              </Button>
            </span>
          </Tooltip>

          {blocked && canOverride && gate.overridable && (
            <Button variant="ghost" size="sm" onClick={() => setOverrideOpen(true)}>
              Request override
            </Button>
          )}
        </div>

        <GatePanel gate={gate} />
      </div>

      <Modal
        open={overrideOpen}
        onOpenChange={setOverrideOpen}
        title="Override this gate?"
        description="This is recorded in the Override Register permanently, with your name against it."
        footer={
          <>
            <Button variant="secondary" onClick={() => setOverrideOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              loading={override.processing}
              disabled={override.data.override_reason.trim().length < 20}
              onClick={() =>
                override.post(action, {
                  preserveScroll: true,
                  onSuccess: () => {
                    override.reset()
                    setOverrideOpen(false)
                  },
                })
              }
            >
              Override and proceed
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <div className="rounded-card border border-danger bg-danger-bg p-4">
            <p className="flex items-center gap-2 text-h3 text-danger">
              <ShieldAlert className="size-4" aria-hidden />
              What you are overriding
            </p>
            <ul className="mt-2 flex flex-col gap-1">
              {gate.failures.map((failure) => (
                <li key={`${failure.rule}-${failure.condition}`} className="text-body text-ink-soft">
                  {failure.message}
                </li>
              ))}
            </ul>
          </div>

          <Textarea
            label="Why are you overriding this?"
            required
            rows={4}
            value={override.data.override_reason}
            error={override.errors.override_reason}
            help="At least 20 characters. This is read by whoever audits the register later."
            onChange={(event) => override.setData('override_reason', event.target.value)}
          />
        </div>
      </Modal>
    </>
  )
}

/**
 * The explanation itself: one line per failed condition, each with a link to
 * the screen that resolves it.
 */
export function GatePanel({ gate }: { gate: GateResult }) {
  if (gate.verdict === 'pass' || gate.failures.length === 0) return null

  const blocked = gate.verdict === 'block'

  return (
    <div
      className={cn(
        'rounded-card border p-4',
        blocked ? 'border-danger bg-danger-bg' : 'border-warning bg-warning-bg',
      )}
    >
      <p className={cn('flex items-center gap-2 text-h3', blocked ? 'text-danger' : 'text-warning')}>
        {blocked ? <ShieldAlert className="size-4" aria-hidden /> : <AlertTriangle className="size-4" aria-hidden />}
        {blocked ? 'This is blocked' : 'Proceed with care'}
      </p>

      <ul className="mt-3 flex flex-col gap-3">
        {gate.failures.map((failure) => (
          <li key={`${failure.rule}-${failure.condition}`} className="flex flex-wrap items-baseline gap-2">
            <span className={cn('mt-1 size-[6px] shrink-0 rounded-full', blocked ? 'bg-danger' : 'bg-warning')} aria-hidden />
            <span className="min-w-0 flex-1 text-body text-ink">{failure.message}</span>
            {failure.resolution && (
              <Link href={failure.resolution.href} className="text-small text-accent-ink hover:underline">
                {failure.resolution.label}
              </Link>
            )}
          </li>
        ))}
      </ul>
    </div>
  )
}

/** A quiet confirmation that a guarded step has been cleared. */
export function GateCleared({ label }: { label: string }) {
  return (
    <p className="flex items-center gap-2 rounded-card border border-success bg-success-bg px-4 py-3 text-small text-success">
      <ShieldCheck className="size-4" aria-hidden />
      {label}
    </p>
  )
}
