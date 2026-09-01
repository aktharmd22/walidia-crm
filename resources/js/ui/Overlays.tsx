import { Fragment, type ReactNode } from 'react'
import * as Dialog from '@radix-ui/react-dialog'
import * as DropdownMenuPrimitive from '@radix-ui/react-dropdown-menu'
import * as TooltipPrimitive from '@radix-ui/react-tooltip'
import * as TabsPrimitive from '@radix-ui/react-tabs'
import { X } from 'lucide-react'
import { cn } from '@/lib/cn'

/* ── Modal ──────────────────────────────────────────────────────────────── */

export function Modal({
  open,
  onOpenChange,
  title,
  description,
  children,
  footer,
  size = 'md',
}: {
  open: boolean
  onOpenChange: (open: boolean) => void
  title: string
  description?: string
  children: ReactNode
  footer?: ReactNode
  size?: 'sm' | 'md' | 'lg'
}) {
  const width = { sm: 'max-w-[420px]', md: 'max-w-[560px]', lg: 'max-w-[760px]' }[size]

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-modal bg-ink/40" />
        <Dialog.Content
          className={cn(
            'fixed z-modal inset-x-4 top-1/2 -translate-y-1/2 mx-auto w-auto',
            'bg-hull rounded-shell shadow-modal border border-line',
            'focus:outline-none',
            width,
          )}
        >
          <div className="flex items-start justify-between gap-4 px-5 py-4 border-b border-line">
            <div>
              <Dialog.Title className="text-h2 text-ink">{title}</Dialog.Title>
              {description && (
                <Dialog.Description className="mt-1 text-small text-ink-soft">{description}</Dialog.Description>
              )}
            </div>
            <Dialog.Close
              className="rounded-pill p-1 text-ink-faint hover:bg-deck hover:text-ink"
              aria-label="Close"
            >
              <X className="size-4" aria-hidden />
            </Dialog.Close>
          </div>
          <div className="px-5 py-4 max-h-[70vh] overflow-y-auto">{children}</div>
          {footer && <div className="flex justify-end gap-3 px-5 py-4 border-t border-line">{footer}</div>}
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  )
}

/* ── Drawer — create/edit on desktop, full page below md ────────────────── */

export function Drawer({
  open,
  onOpenChange,
  title,
  description,
  children,
  footer,
  width = 'md',
}: {
  open: boolean
  onOpenChange: (open: boolean) => void
  title: string
  description?: string
  children: ReactNode
  footer?: ReactNode
  width?: 'md' | 'lg'
}) {
  const size = { md: 'md:max-w-[480px]', lg: 'md:max-w-[640px]' }[width]

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-drawer bg-ink/40" />
        <Dialog.Content
          className={cn(
            'fixed z-drawer inset-y-0 end-0 w-full bg-hull shadow-modal border-s border-line',
            'flex flex-col focus:outline-none',
            size,
          )}
        >
          <div className="flex items-start justify-between gap-4 px-5 py-4 border-b border-line">
            <div>
              <Dialog.Title className="text-h2 text-ink">{title}</Dialog.Title>
              {description && (
                <Dialog.Description className="mt-1 text-small text-ink-soft">{description}</Dialog.Description>
              )}
            </div>
            <Dialog.Close className="rounded-pill p-1 text-ink-faint hover:bg-deck hover:text-ink" aria-label="Close">
              <X className="size-4" aria-hidden />
            </Dialog.Close>
          </div>
          <div className="flex-1 overflow-y-auto px-5 py-4">{children}</div>
          {footer && (
            <div className="flex justify-end gap-3 px-5 py-4 border-t border-line bg-hull">{footer}</div>
          )}
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  )
}

/* ── Dropdown menu — the row "…" menu from the reference UI ─────────────── */

export interface MenuItem {
  key: string
  label: string
  icon?: ReactNode
  onSelect?: () => void
  href?: string
  destructive?: boolean
  disabled?: boolean
  separatorBefore?: boolean
}

export function DropdownMenu({
  trigger,
  items,
  label,
  align = 'end',
}: {
  trigger: ReactNode
  items: MenuItem[]
  label?: string
  align?: 'start' | 'end'
}) {
  return (
    <DropdownMenuPrimitive.Root>
      <DropdownMenuPrimitive.Trigger asChild>{trigger}</DropdownMenuPrimitive.Trigger>
      <DropdownMenuPrimitive.Portal>
        <DropdownMenuPrimitive.Content
          align={align}
          sideOffset={6}
          className="z-popover min-w-[200px] rounded-shell border border-line bg-hull p-1 shadow-pop"
        >
          {label && (
            <DropdownMenuPrimitive.Label className="px-3 py-2 text-micro text-ink-faint">
              {label}
            </DropdownMenuPrimitive.Label>
          )}
          {items.map((item) => (
            <Fragment key={item.key}>
              {item.separatorBefore && (
                <DropdownMenuPrimitive.Separator className="my-1 h-px bg-line" />
              )}
              <DropdownMenuPrimitive.Item
                disabled={item.disabled}
                onSelect={item.onSelect}
                asChild={Boolean(item.href)}
                className={cn(
                  'flex items-center gap-3 rounded-card px-3 py-2 text-body cursor-pointer outline-none',
                  'data-[highlighted]:bg-deck data-[disabled]:opacity-50 data-[disabled]:cursor-not-allowed',
                  item.destructive ? 'text-danger' : 'text-ink',
                )}
              >
                {item.href ? (
                  <a href={item.href}>
                    {item.icon}
                    {item.label}
                  </a>
                ) : (
                  <>
                    {item.icon}
                    {item.label}
                  </>
                )}
              </DropdownMenuPrimitive.Item>
            </Fragment>
          ))}
        </DropdownMenuPrimitive.Content>
      </DropdownMenuPrimitive.Portal>
    </DropdownMenuPrimitive.Root>
  )
}

/* ── Tooltip ────────────────────────────────────────────────────────────── */

export function TooltipProvider({ children }: { children: ReactNode }) {
  return <TooltipPrimitive.Provider delayDuration={200}>{children}</TooltipPrimitive.Provider>
}

export function Tooltip({ content, children }: { content: ReactNode; children: ReactNode }) {
  if (!content) return <>{children}</>

  return (
    <TooltipPrimitive.Root>
      <TooltipPrimitive.Trigger asChild>{children}</TooltipPrimitive.Trigger>
      <TooltipPrimitive.Portal>
        <TooltipPrimitive.Content
          sideOffset={6}
          className="z-popover max-w-[280px] rounded-card bg-ink px-3 py-2 text-small text-white shadow-pop"
        >
          {content}
          <TooltipPrimitive.Arrow className="fill-[color:var(--ink)]" />
        </TooltipPrimitive.Content>
      </TooltipPrimitive.Portal>
    </TooltipPrimitive.Root>
  )
}

/* ── Scope tabs — the segmented control from the reference UI ───────────── */

export function Tabs({
  value,
  onValueChange,
  items,
  className,
}: {
  value: string
  onValueChange: (value: string) => void
  items: { value: string; label: string; icon?: ReactNode; count?: number }[]
  className?: string
}) {
  return (
    <TabsPrimitive.Root value={value} onValueChange={onValueChange} className={className}>
      <TabsPrimitive.List className="inline-flex gap-1 rounded-shell border border-line bg-deck p-1">
        {items.map((item) => (
          <TabsPrimitive.Trigger
            key={item.value}
            value={item.value}
            className={cn(
              'inline-flex items-center gap-2 rounded-card px-3 py-2 text-h3 text-ink-soft',
              'transition-colors duration-fast ease-std hover:text-ink',
              'data-[state=active]:bg-hull data-[state=active]:text-ink data-[state=active]:shadow-pop',
            )}
          >
            {item.icon}
            {item.label}
            {typeof item.count === 'number' && (
              <span className="numeric rounded-pill bg-line px-2 text-micro text-ink-soft">{item.count}</span>
            )}
          </TabsPrimitive.Trigger>
        ))}
      </TabsPrimitive.List>
    </TabsPrimitive.Root>
  )
}
