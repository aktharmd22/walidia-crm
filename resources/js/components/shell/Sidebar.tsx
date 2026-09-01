import { useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { ChevronDown, ChevronsUpDown, LogOut, Moon, Palette, Sun, X } from 'lucide-react'
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

function NavSectionItem({
  section,
  currentUrl,
  collapsed,
}: {
  section: NavSection
  currentUrl: string
  collapsed: boolean
}) {
  const Icon = navIcon(section.icon)
  const sectionActive =
    isActive(currentUrl, section.href) || section.children.some((child) => isActive(currentUrl, child.href))
  const [open, setOpen] = useState(sectionActive)

  const hasChildren = section.children.length > 0
  const rowClass = cn(
    'group flex items-center gap-3 w-full rounded-card px-3 h-[36px] text-h3 transition-colors duration-fast ease-std',
    sectionActive
      ? 'bg-[color:var(--sidebar-active-bg)] text-[color:var(--sidebar-fg-active)]'
      : 'text-[color:var(--sidebar-fg)] hover:bg-[color:var(--sidebar-hover-bg)] hover:text-[color:var(--sidebar-fg-active)]',
    collapsed && 'justify-center px-0',
  )

  if (!hasChildren) {
    return (
      <Link href={section.href ?? '#'} className={rowClass} title={collapsed ? section.label : undefined}>
        <Icon className="size-4 shrink-0" aria-hidden />
        {!collapsed && <span className="truncate">{section.label}</span>}
      </Link>
    )
  }

  return (
    <div>
      <button
        type="button"
        onClick={() => (collapsed ? undefined : setOpen((value) => !value))}
        className={rowClass}
        aria-expanded={collapsed ? undefined : open}
        title={collapsed ? section.label : undefined}
      >
        <Icon className="size-4 shrink-0" aria-hidden />
        {!collapsed && (
          <>
            <span className="truncate flex-1 text-start">{section.label}</span>
            <ChevronDown
              className={cn('size-3 shrink-0 transition-transform duration-fast', open && 'rotate-180')}
              aria-hidden
            />
          </>
        )}
      </button>

      {!collapsed && open && (
        <ul className="mt-1 ms-4 flex flex-col gap-px border-s border-[color:var(--sidebar-line)] ps-3">
          {section.children.map((child) => (
            <li key={child.key}>
              <Link
                href={child.href}
                className={cn(
                  'flex items-center justify-between gap-2 rounded-card px-3 py-2 text-body transition-colors duration-fast',
                  isActive(currentUrl, child.href)
                    ? 'text-[color:var(--sidebar-fg-active)] bg-[color:var(--sidebar-hover-bg)]'
                    : 'text-[color:var(--sidebar-fg)] hover:text-[color:var(--sidebar-fg-active)] hover:bg-[color:var(--sidebar-hover-bg)]',
                )}
              >
                <span className="truncate">{child.label}</span>
                {typeof child.badge === 'number' && child.badge > 0 && (
                  <span className="numeric rounded-pill bg-[color:var(--sidebar-hover-bg)] px-2 text-micro">
                    {child.badge}
                  </span>
                )}
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

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

  return (
    <aside
      className={cn(
        'flex h-full flex-col bg-[color:var(--sidebar-bg)] border-e border-[color:var(--sidebar-line)]',
        collapsed ? 'w-rail' : 'w-sidebar',
      )}
    >
      {/* Workspace switcher */}
      <div className="flex items-center gap-3 border-b border-[color:var(--sidebar-line)] p-3">
        <span className="grid size-8 shrink-0 place-items-center rounded-card bg-accent text-accent-on text-h3">
          W
        </span>
        {!collapsed && (
          <>
            <span className="min-w-0 flex-1">
              <span className="block truncate text-h3 text-[color:var(--sidebar-fg-active)]">{app.name}</span>
              <span className="block truncate text-micro text-[color:var(--sidebar-fg-muted)]">
                Charter · Brokerage · Management
              </span>
            </span>
            <ChevronsUpDown className="size-4 shrink-0 text-[color:var(--sidebar-fg-muted)]" aria-hidden />
          </>
        )}
        {mobile && (
          <button
            type="button"
            onClick={onClose}
            className="rounded-pill p-1 text-[color:var(--sidebar-fg-muted)] hover:text-[color:var(--sidebar-fg-active)]"
            aria-label="Close navigation"
          >
            <X className="size-4" aria-hidden />
          </button>
        )}
      </div>

      {/* Nav */}
      <nav className="flex-1 overflow-y-auto p-3" aria-label="Main">
        {!collapsed && <p className="px-3 pb-2 text-micro text-[color:var(--sidebar-fg-muted)]">MENU</p>}
        <div className="flex flex-col gap-px">
          {nav.map((section) => (
            <NavSectionItem key={section.key} section={section} currentUrl={url} collapsed={collapsed} />
          ))}
        </div>
      </nav>

      {/* User card */}
      <div className="border-t border-[color:var(--sidebar-line)] p-3">
        <DropdownMenu
          align="start"
          label="ACCOUNT"
          trigger={
            <button
              type="button"
              className={cn(
                'flex w-full items-center gap-3 rounded-card p-2 text-start transition-colors duration-fast',
                'hover:bg-[color:var(--sidebar-hover-bg)]',
                collapsed && 'justify-center',
              )}
            >
              <Avatar name={auth.user?.name} src={auth.user?.avatar_url} size="sm" />
              {!collapsed && (
                <span className="min-w-0 flex-1">
                  <span className="block truncate text-h3 text-[color:var(--sidebar-fg-active)]">
                    {auth.user?.name}
                  </span>
                  <span className="block truncate text-micro text-[color:var(--sidebar-fg-muted)]">
                    {auth.user?.email}
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
