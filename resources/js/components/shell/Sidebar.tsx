import { useEffect, useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { ChevronDown, LogOut, Moon, Palette, Sun, X } from 'lucide-react'
import { cn } from '@/lib/cn'
import { navIcon } from '@/lib/icons'
import { Avatar } from '@/ui/Primitives'
import { DropdownMenu } from '@/ui/Overlays'
import type { NavSection, SharedProps } from '@/types'

function isActive(currentUrl: string, href: string | null): boolean {
  if (!href) return false
  if (href === '/') return currentUrl === '/'
  return currentUrl === href || currentUrl.startsWith(`${href}/`) || currentUrl.startsWith(`${href}?`)
}

/**
 * One row.
 *
 * The active state is a filled row plus a brass rail on the leading edge. The
 * rail matters: on a long list, a colour change alone is hard to find, and the
 * eye tracks a vertical mark down the edge far faster than it re-reads labels.
 */
function NavRow({
  section,
  currentUrl,
  collapsed,
  onNavigate,
}: {
  section: NavSection
  currentUrl: string
  collapsed: boolean
  onNavigate?: () => void
}) {
  const Icon = navIcon(section.icon)
  const childActive = section.children.some((child) => isActive(currentUrl, child.href))
  const active = isActive(currentUrl, section.href) || childActive
  const [open, setOpen] = useState(childActive)

  // A section whose child becomes current opens itself — following a link from
  // elsewhere should not leave the nav pointing at nothing.
  useEffect(() => {
    if (childActive) setOpen(true)
  }, [childActive])

  const hasChildren = section.children.length > 0

  const row = cn(
    'group relative flex h-[38px] w-full items-center gap-3 rounded-card px-3 text-h3',
    'transition-colors duration-fast ease-std',
    active
      ? 'bg-[color:var(--sidebar-active-bg)] text-[color:var(--sidebar-fg-active)]'
      : 'text-[color:var(--sidebar-fg)] hover:bg-[color:var(--sidebar-hover-bg)] hover:text-[color:var(--sidebar-fg-active)]',
    collapsed && 'justify-center px-0',
  )

  const rail = active && (
    <span
      className="absolute inset-y-1 start-0 w-[3px] rounded-e-full bg-accent"
      aria-hidden
    />
  )

  if (!hasChildren) {
    return (
      <Link href={section.href ?? '#'} className={row} title={collapsed ? section.label : undefined} onClick={onNavigate}>
        {rail}
        <Icon className={cn('size-[18px] shrink-0', active && 'text-accent')} aria-hidden />
        {!collapsed && <span className="truncate">{section.label}</span>}
      </Link>
    )
  }

  return (
    <div>
      <button
        type="button"
        onClick={() => (collapsed ? undefined : setOpen((value) => !value))}
        className={row}
        aria-expanded={collapsed ? undefined : open}
        title={collapsed ? section.label : undefined}
      >
        {rail}
        <Icon className={cn('size-[18px] shrink-0', active && 'text-accent')} aria-hidden />
        {!collapsed && (
          <>
            <span className="flex-1 truncate text-start">{section.label}</span>
            <ChevronDown
              className={cn(
                'size-[14px] shrink-0 text-[color:var(--sidebar-fg-muted)] transition-transform duration-fast motion-reduce:transition-none',
                open && 'rotate-180',
              )}
              aria-hidden
            />
          </>
        )}
      </button>

      {!collapsed && open && (
        <ul className="relative mt-1 ms-[26px] flex flex-col gap-px ps-3 before:absolute before:inset-y-1 before:start-0 before:w-px before:bg-[color:var(--sidebar-line)]">
          {section.children.map((child) => {
            const childIsActive = isActive(currentUrl, child.href)

            return (
              <li key={child.key}>
                <Link
                  href={child.href}
                  onClick={onNavigate}
                  className={cn(
                    'flex items-center justify-between gap-2 rounded-card px-3 py-[7px] text-body',
                    'transition-colors duration-fast ease-std',
                    childIsActive
                      ? 'bg-[color:var(--sidebar-hover-bg)] font-medium text-[color:var(--sidebar-fg-active)]'
                      : 'text-[color:var(--sidebar-fg)] hover:bg-[color:var(--sidebar-hover-bg)] hover:text-[color:var(--sidebar-fg-active)]',
                  )}
                >
                  <span className="truncate">{child.label}</span>
                  {typeof child.badge === 'number' && child.badge > 0 && (
                    <span className="numeric shrink-0 rounded-pill bg-accent-soft px-[6px] text-micro text-accent">
                      {child.badge}
                    </span>
                  )}
                </Link>
              </li>
            )
          })}
        </ul>
      )}
    </div>
  )
}

/**
 * The sidebar.
 *
 * Seventeen destinations is too many for one column, so they are banded into
 * named groups — Overview, Revenue, Operations, Business, Admin — which is how
 * the company is actually organised. Collapsed, the groups become dividers and
 * the labels become tooltips; the shape survives either way.
 */
export function Sidebar({
  collapsed = false,
  onClose,
  mobile = false,
}: {
  collapsed?: boolean
  onClose?: () => void
  mobile?: boolean
}) {
  const { props, url } = usePage<SharedProps>()
  const { nav, auth, app, chrome } = props

  // Preserve the order the server sent, rather than an alphabetical accident.
  const groups: { name: string; sections: NavSection[] }[] = []

  for (const section of nav) {
    const name = section.group ?? 'Overview'
    const existing = groups.find((group) => group.name === name)

    if (existing) {
      existing.sections.push(section)
    } else {
      groups.push({ name, sections: [section] })
    }
  }

  return (
    <aside
      className={cn(
        'flex h-full flex-col bg-[color:var(--sidebar-bg)] border-e border-[color:var(--sidebar-line)]',
        collapsed ? 'w-rail' : 'w-sidebar',
      )}
    >
      {/* Identity */}
      <div
        className={cn(
          'flex h-topbar shrink-0 items-center gap-3 border-b border-[color:var(--sidebar-line)] px-4',
          collapsed && 'justify-center px-0',
        )}
      >
        <Link href="/" className="flex min-w-0 items-center gap-3" aria-label={app.name}>
          <img src="/favicon-192.png" alt="" aria-hidden className="size-8 shrink-0 object-contain" />
          {!collapsed && (
            <span className="min-w-0">
              <span className="block truncate text-h3 leading-tight text-[color:var(--sidebar-fg-active)]">
                {app.name}
              </span>
              <span className="block truncate text-micro text-[color:var(--sidebar-fg-muted)]">
                Charter · Brokerage · Management
              </span>
            </span>
          )}
        </Link>

        {mobile && (
          <button
            type="button"
            onClick={onClose}
            className="ms-auto rounded-pill p-[6px] text-[color:var(--sidebar-fg-muted)] hover:bg-[color:var(--sidebar-hover-bg)] hover:text-[color:var(--sidebar-fg-active)]"
            aria-label="Close navigation"
          >
            <X className="size-4" aria-hidden />
          </button>
        )}
      </div>

      {/* Destinations */}
      <nav className="flex-1 overflow-y-auto px-3 py-4" aria-label="Main">
        {groups.map((group, index) => (
          <div key={group.name} className={cn(index > 0 && 'mt-5')}>
            {collapsed ? (
              index > 0 && <hr className="mx-auto mb-2 w-6 border-[color:var(--sidebar-line)]" />
            ) : (
              <p className="px-3 pb-[6px] text-micro uppercase tracking-[0.08em] text-[color:var(--sidebar-fg-muted)]">
                {group.name}
              </p>
            )}

            <div className="flex flex-col gap-[2px]">
              {group.sections.map((section) => (
                <NavRow
                  key={section.key}
                  section={section}
                  currentUrl={url}
                  collapsed={collapsed}
                  onNavigate={mobile ? onClose : undefined}
                />
              ))}
            </div>
          </div>
        ))}
      </nav>

      {/* Who is signed in */}
      <div className="shrink-0 border-t border-[color:var(--sidebar-line)] p-3">
        <DropdownMenu
          align="start"
          label="ACCOUNT"
          trigger={
            <button
              type="button"
              className={cn(
                'flex w-full items-center gap-3 rounded-card p-2 text-start',
                'transition-colors duration-fast ease-std hover:bg-[color:var(--sidebar-hover-bg)]',
                collapsed && 'justify-center p-1',
              )}
            >
              <Avatar name={auth.user?.name} src={auth.user?.avatar_url} size="sm" />
              {!collapsed && (
                <span className="min-w-0 flex-1">
                  <span className="block truncate text-h3 leading-tight text-[color:var(--sidebar-fg-active)]">
                    {auth.user?.name}
                  </span>
                  <span className="block truncate text-micro text-[color:var(--sidebar-fg-muted)]">
                    {auth.user?.roles?.[0] ?? auth.user?.email}
                  </span>
                </span>
              )}
            </button>
          }
          items={[
            { key: 'profile', label: 'Profile & security', href: '/me/profile' },
            { key: 'sessions', label: 'Active sessions', href: '/me/sessions' },
            {
              key: 'chrome',
              label: chrome.theme === 'navy' ? 'Switch to light chrome' : 'Switch to navy chrome',
              icon: chrome.theme === 'navy' ? <Sun className="size-4" /> : <Moon className="size-4" />,
              onSelect: () => router.post(`/me/chrome/${chrome.theme === 'navy' ? 'light' : 'navy'}`),
            },
            {
              key: 'accent',
              label: chrome.accent === 'brass' ? 'Use blue accent' : 'Use brass accent',
              icon: <Palette className="size-4" />,
              onSelect: () => router.post(`/me/accent/${chrome.accent === 'brass' ? 'blue' : 'brass'}`),
            },
            {
              key: 'logout',
              label: 'Sign out',
              icon: <LogOut className="size-4" />,
              onSelect: () => router.post('/logout'),
              destructive: true,
              separatorBefore: true,
            },
          ]}
        />
      </div>
    </aside>
  )
}
