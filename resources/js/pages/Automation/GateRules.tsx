import { Head, router } from '@inertiajs/react'
import { useState } from 'react'
import { Gauge } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Card, CardBody, CardHeader, CardTitle, EmptyState, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { Tabs } from '@/ui/Overlays'

interface Rule {
  id: number
  key: string
  name: string
  subject_type: string
  trigger: string | null
  severity: 'hard' | 'soft'
  conditions: { check: string; params?: Record<string, unknown>; message_en?: string }[]
  block_message: string
  is_active: boolean
  is_overridable: boolean
  version: number
}

/**
 * "What unlocks what", as an editable list.
 *
 * The rules are data (D-004): switching one off is a toggle, and every change
 * is audited and versioned — so "who loosened the boarding gate, and when" has
 * an answer.
 */
export default function GateRules({ rules = [], checks = [] }: { rules?: Rule[]; checks?: string[] }) {
  const [scope, setScope] = useState('all')

  const visible = rules.filter((rule) =>
    scope === 'all' ? true : scope === 'hard' ? rule.severity === 'hard' : rule.severity === 'soft',
  )

  return (
    <>
      <Head title="Gate rules" />

      <PageHeader
        title="Gate rules"
        description="Hard gates block a transition until their conditions pass. Soft gates allow it, warn, and raise a task."
      />

      <Tabs
        value={scope}
        onValueChange={setScope}
        items={[
          { value: 'all', label: 'All rules', count: rules.length },
          { value: 'hard', label: 'Hard', count: rules.filter((rule) => rule.severity === 'hard').length },
          { value: 'soft', label: 'Soft', count: rules.filter((rule) => rule.severity === 'soft').length },
        ]}
      />

      <Card>
        {visible.length === 0 ? (
          <EmptyState icon={<Gauge className="size-5" aria-hidden />} title="No rules" />
        ) : (
          <ul className="divide-y divide-line">
            {visible.map((rule) => (
              <li key={rule.id} className="flex flex-col gap-3 px-5 py-4">
                <div className="flex flex-wrap items-center gap-3">
                  <StatusPill tone={rule.severity === 'hard' ? 'danger' : 'warning'}>
                    {rule.severity === 'hard' ? 'Hard' : 'Soft'}
                  </StatusPill>
                  <span className="text-h3 text-ink">{rule.name}</span>
                  <span className="numeric text-small text-ink-faint">{rule.key}</span>
                  {!rule.is_active && <StatusPill tone="neutral">Switched off</StatusPill>}
                  {!rule.is_overridable && <StatusPill tone="attention">No override</StatusPill>}

                  <span className="ms-auto flex items-center gap-3">
                    <span className="text-micro text-ink-faint">
                      v<Num value={rule.version} />
                    </span>
                    <Button
                      size="sm"
                      variant={rule.is_active ? 'secondary' : 'primary'}
                      onClick={() => router.post(`/automation/gate-rules/${rule.id}/toggle`, {}, { preserveScroll: true })}
                    >
                      {rule.is_active ? 'Switch off' : 'Switch on'}
                    </Button>
                  </span>
                </div>

                <p className="text-body text-ink-soft">{rule.block_message}</p>

                <div className="flex flex-wrap items-center gap-2 text-small text-ink-faint">
                  <span className="rounded-pill bg-deck px-2 py-px text-micro">{rule.subject_type}</span>
                  <span className="numeric">{rule.trigger}</span>
                  {rule.conditions.map((condition) => (
                    <span key={condition.check} className="numeric rounded-pill bg-accent-soft px-2 py-px text-micro text-accent">
                      {condition.check}
                    </span>
                  ))}
                </div>
              </li>
            ))}
          </ul>
        )}
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Available checks</CardTitle>
          <span className="text-small text-ink-faint">
            <Num value={checks.length} /> implemented
          </span>
        </CardHeader>
        <CardBody>
          <p className="mb-3 text-body text-ink-soft">
            A rule may only refer to a check that exists. Adding a new kind of check is a code change; adding,
            retargeting or switching off a rule is not.
          </p>
          <div className="flex flex-wrap gap-2">
            {checks.map((check) => (
              <span key={check} className="numeric rounded-pill border border-line px-2 py-px text-micro text-ink-soft">
                {check}
              </span>
            ))}
          </div>
        </CardBody>
      </Card>
    </>
  )
}
