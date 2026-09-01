import { Link, usePage } from '@inertiajs/react'
import { Bell, Menu, Search, Zap } from 'lucide-react'
import { cn } from '@/lib/cn'
import { Avatar } from '@/ui/Primitives'
import type { SharedProps } from '@/types'

export interface Crumb {
  label: string
  href?: string
}

export function Topbar({
  crumbs = [],
  onOpenNav,
  onOpenSearch,
}: {
  crumbs?: Crumb[]
  onOpenNav: () => void
  onOpenSearch: () => void
}) {
  const { props } = usePage<SharedProps>()
  const user = props.auth.user

  return (
    <header className="sticky top-0 z-sticky flex h-topbar items-center gap-3 border-b border-line bg-hull px-4 lg:px-6">
      <button
        type="button"
        onClick={onOpenNav}
        className="rounded-card p-2 text-ink-soft hover:bg-deck md:hidden"
        aria-label="Open navigation"
      >
        <Menu className="size-5" aria-hidden />
      </button>

      <nav aria-label="Breadcrumb" className="min-w-0 flex-1">
        <ol className="flex items-center gap-2 text-small text-ink-faint">
          {crumbs.map((crumb, index) => (
            <li key={`${crumb.label}-${index}`} className="flex items-center gap-2 min-w-0">
              {index > 0 && <span aria-hidden>/</span>}
              {crumb.href ? (
                <Link href={crumb.href} className="truncate hover:text-ink">
                  {crumb.label}
                </Link>
              ) : (
                <span className={cn('truncate', index === crumbs.length - 1 && 'text-ink-soft')}>{crumb.label}</span>
              )}
            </li>
          ))}
        </ol>
      </nav>

      <button
        type="button"
        onClick={onOpenSearch}
        className="hidden h-field w-[280px] items-center gap-2 rounded-card border border-line bg-deck px-3 text-small text-ink-faint hover:border-line-strong lg:flex"
      >
        <Search className="size-4" aria-hidden />
        <span className="flex-1 text-start">Search clients, yachts, bookings…</span>
        <kbd className="rounded-pill border border-line bg-hull px-2 text-micro text-ink-faint">⌘K</kbd>
      </button>

      <button
        type="button"
        onClick={onOpenSearch}
        className="rounded-card p-2 text-ink-soft hover:bg-deck lg:hidden"
        aria-label="Search"
      >
        <Search className="size-5" aria-hidden />
      </button>

      <Link
        href="/tasks/create"
        className="hidden rounded-card p-2 text-ink-soft hover:bg-deck sm:block"
        aria-label="Quick create"
      >
        <Zap className="size-5" aria-hidden />
      </Link>

      <Link href="/notifications" className="relative rounded-card p-2 text-ink-soft hover:bg-deck" aria-label="Alerts">
        <Bell className="size-5" aria-hidden />
        <span className="absolute end-2 top-2 size-2 rounded-full bg-danger" aria-hidden />
      </Link>

      <Link href="/me/profile" className="flex items-center gap-2 rounded-card p-1 hover:bg-deck">
        <Avatar name={user?.name} src={user?.avatar_url} size="sm" />
        <span className="hidden text-h3 text-ink lg:block">{user?.name?.split(' ')[0]}</span>
      </Link>
    </header>
  )
}
