import { useEffect, useState } from 'react'
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  Cell,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { cn } from '@/lib/cn'

/**
 * Charts, on the same terms as the rest of the system.
 *
 * Three rules hold across all of them. They read their colours from the CSS
 * custom properties, so a chart follows the chrome and the accent instead of
 * hard-coding a palette that drifts. They carry no gridlines, borders or
 * legends they do not need — a figure the eye has to hunt for is a figure
 * nobody reads. And they respect `prefers-reduced-motion`, because a dashboard
 * that animates on every poll is a dashboard people close.
 */

/** Reads a token at runtime, so the chart follows the theme and the toggle. */
function token(name: string, fallback: string): string {
  if (typeof window === 'undefined') return fallback

  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()

  return value === '' ? fallback : value
}

function usePalette() {
  const [palette, setPalette] = useState({
    accent: '#B8894A',
    ink: '#0F1B2D',
    line: '#E5E7EB',
    faint: '#9CA3AF',
    success: '#2F855A',
    info: '#2B6CB0',
    warning: '#B7791F',
    danger: '#C53030',
  })

  useEffect(() => {
    const read = () =>
      setPalette({
        accent: token('--accent', '#B8894A'),
        ink: token('--ink', '#0F1B2D'),
        line: token('--line', '#E5E7EB'),
        faint: token('--ink-faint', '#9CA3AF'),
        success: token('--success', '#2F855A'),
        info: token('--info', '#2B6CB0'),
        warning: token('--warning', '#B7791F'),
        danger: token('--danger', '#C53030'),
      })

    read()

    // The chrome and accent are attributes on <html>; follow them.
    const observer = new MutationObserver(read)
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-chrome', 'data-accent', 'data-theme'] })

    return () => observer.disconnect()
  }, [])

  return palette
}

function useReducedMotion(): boolean {
  const [reduced, setReduced] = useState(false)

  useEffect(() => {
    const query = window.matchMedia('(prefers-reduced-motion: reduce)')
    const update = () => setReduced(query.matches)

    update()
    query.addEventListener('change', update)

    return () => query.removeEventListener('change', update)
  }, [])

  return reduced
}

interface TooltipEntry {
  name?: string
  value?: number | string
  color?: string
}

/** One tooltip for every chart, so a hover always looks the same. */
function ChartTooltip({
  active,
  payload,
  label,
  format,
}: {
  active?: boolean
  payload?: TooltipEntry[]
  label?: string | number
  format?: (value: number) => string
}) {
  if (!active || !payload?.length) return null

  return (
    <div className="rounded-card border border-line bg-hull px-3 py-2 shadow-sm">
      {label !== undefined && <p className="mb-1 text-micro uppercase tracking-wide text-ink-faint">{label}</p>}
      {payload.map((entry) => (
        <p key={String(entry.name)} className="flex items-center gap-2 text-small text-ink">
          <span className="size-2 shrink-0 rounded-full" style={{ background: entry.color }} aria-hidden />
          <span className="text-ink-soft">{entry.name}</span>
          <span className="numeric ms-auto font-medium">
            {typeof entry.value === 'number' && format ? format(entry.value) : entry.value}
          </span>
        </p>
      ))}
    </div>
  )
}

export interface SeriesPoint {
  label: string
  [key: string]: string | number
}

export interface Series {
  key: string
  name: string
  tone?: 'accent' | 'ink' | 'success' | 'info' | 'warning' | 'danger'
}

/**
 * The trend chart. Stacked areas by default, because the question a director
 * asks of revenue is "how much, and from where" in one glance.
 */
export function TrendChart({
  data,
  series,
  height = 260,
  format,
  stacked = true,
}: {
  data: SeriesPoint[]
  series: Series[]
  height?: number
  format?: (value: number) => string
  stacked?: boolean
}) {
  const palette = usePalette()
  const reduced = useReducedMotion()
  const colour = (tone: Series['tone']) => palette[tone ?? 'accent']

  return (
    <div style={{ height }} className="w-full">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 8, right: 8, bottom: 0, left: -18 }}>
          <defs>
            {series.map((item) => (
              <linearGradient key={item.key} id={`fill-${item.key}`} x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor={colour(item.tone)} stopOpacity={0.28} />
                <stop offset="100%" stopColor={colour(item.tone)} stopOpacity={0.02} />
              </linearGradient>
            ))}
          </defs>

          <XAxis
            dataKey="label"
            tickLine={false}
            axisLine={false}
            tick={{ fill: palette.faint, fontSize: 11 }}
            dy={8}
          />
          <YAxis
            tickLine={false}
            axisLine={false}
            tick={{ fill: palette.faint, fontSize: 11 }}
            width={56}
            tickFormatter={(value: number) => (format ? format(value) : String(value))}
          />
          <Tooltip content={<ChartTooltip format={format} />} cursor={{ stroke: palette.line, strokeWidth: 1 }} />

          {series.map((item) => (
            <Area
              key={item.key}
              type="monotone"
              dataKey={item.key}
              name={item.name}
              stackId={stacked ? 'one' : undefined}
              stroke={colour(item.tone)}
              strokeWidth={2}
              fill={`url(#fill-${item.key})`}
              isAnimationActive={!reduced}
            />
          ))}
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}

/** The mix chart. A donut, because the hole is where the total goes. */
export function DonutChart({
  data,
  height = 220,
  total,
  totalLabel,
}: {
  data: { name: string; value: number; tone?: Series['tone'] }[]
  height?: number
  total?: string
  totalLabel?: string
}) {
  const palette = usePalette()
  const reduced = useReducedMotion()

  return (
    <div className="relative w-full" style={{ height }}>
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={data}
            dataKey="value"
            nameKey="name"
            innerRadius="64%"
            outerRadius="92%"
            paddingAngle={2}
            stroke="none"
            isAnimationActive={!reduced}
          >
            {data.map((entry) => (
              <Cell key={entry.name} fill={palette[entry.tone ?? 'accent']} />
            ))}
          </Pie>
          <Tooltip content={<ChartTooltip />} />
        </PieChart>
      </ResponsiveContainer>

      {total && (
        <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
          <span className="numeric text-h1 text-ink">{total}</span>
          {totalLabel && <span className="text-micro uppercase tracking-wide text-ink-faint">{totalLabel}</span>}
        </div>
      )}
    </div>
  )
}

/**
 * The card sparkline. No axes, no tooltip, no grid — it exists to say "rising
 * or falling", and anything else on it is noise.
 */
export function Sparkline({
  data,
  tone = 'accent',
  height = 40,
  variant = 'line',
}: {
  data: number[]
  tone?: Series['tone']
  height?: number
  variant?: 'line' | 'bar'
}) {
  const palette = usePalette()
  const reduced = useReducedMotion()
  const points = data.map((value, index) => ({ index, value }))
  const stroke = palette[tone]

  if (variant === 'bar') {
    return (
      <div style={{ height, width: 88 }} aria-hidden>
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={points} margin={{ top: 2, right: 0, bottom: 0, left: 0 }}>
            <Bar dataKey="value" fill={stroke} radius={[2, 2, 0, 0]} isAnimationActive={!reduced} />
          </BarChart>
        </ResponsiveContainer>
      </div>
    )
  }

  return (
    <div style={{ height, width: 88 }} aria-hidden>
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={points} margin={{ top: 4, right: 2, bottom: 4, left: 2 }}>
          <Line
            type="monotone"
            dataKey="value"
            stroke={stroke}
            strokeWidth={2}
            dot={false}
            isAnimationActive={!reduced}
          />
        </LineChart>
      </ResponsiveContainer>
    </div>
  )
}

/** A legend that matches the tooltip, rather than Recharts' own. */
export function ChartLegend({ series }: { series: { name: string; tone?: Series['tone'] }[] }) {
  const palette = usePalette()

  return (
    <ul className="flex flex-wrap items-center gap-4">
      {series.map((item) => (
        <li key={item.name} className="flex items-center gap-2 text-small text-ink-soft">
          <span
            className="size-2 shrink-0 rounded-full"
            style={{ background: palette[item.tone ?? 'accent'] }}
            aria-hidden
          />
          {item.name}
        </li>
      ))}
    </ul>
  )
}

/**
 * A horizontal share bar — for "where the money comes from" lists, where the
 * bar is doing the comparing and the number is the detail.
 */
export function ShareBar({ value, tone = 'accent', className }: { value: number; tone?: Series['tone']; className?: string }) {
  const palette = usePalette()

  return (
    <span className={cn('block h-1.5 w-full overflow-hidden rounded-full bg-deck', className)}>
      <span
        className="block h-full rounded-full transition-[width] duration-slow ease-std motion-reduce:transition-none"
        style={{ width: `${Math.min(Math.max(value, 0), 100)}%`, background: palette[tone] }}
      />
    </span>
  )
}
