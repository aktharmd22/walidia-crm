import type { ReactNode } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { Bell, Menu, Moon, Search, Sun } from 'lucide-react'
import { cn } from '@/lib/cn'
import { Avatar } from '@/ui/Primitives'
import type { SharedProps } from '@/types'

export interface Crumb {
  label: string
  href?: string
}

/** The circular outlined control the reference uses along the top bar. */
function RoundButton({
  label,
  onClick,
  href,
  children,
  className,
}: {
  label: string
  onClick?: () => void
  href?: string
  children: ReactNode
  className?: string
}) {
  const classes = cn(
    'relative grid size-[42px] shrink-0 place-items-center rounded-full border border-line bg-hull',
    'text-ink-soft transition-colors duration-fast ease-std hover:border-line-strong hover:text-ink',
    className,
  )

  if (href) {
    return (
      <Link href={href} className={classes} aria-label={label}>
        {children}
      </Link>
    )
  }

  return (
    <button type="button" onClick={onClick} className={classes} aria-label={label}>
      {children}
    </button>
  )
}

function greetingFor(date: Date): string {
  const hour = date.getHours()

  if (hour < 12) return 'Good morning'
  if (hour < 17) return 'Good afternoon'

  return 'Good evening'
}

/**
 * The top bar.
 *
 * Greeting on the left, search in the middle, round controls on the right —
 * the shape of the reference. The breadcrumb lives with the page heading
 * rather than up here, which is also where the reference keeps it.
 */
export function Topbar({
  onOpenNav,
  onOpenSearch,
}: {
  crumbs?: Crumb[]
  onOpenNav: () => void
  onOpenSearch: () => void
}) {
  const { props } = usePage<SharedProps>()
  const user = props.auth.user
  const { chrome } = props

  const firstName = user?.name?.split(' ')[0]

  return (
    <header className="sticky top-0 z-sticky flex h-topbar items-center gap-4 border-b border-line bg-hull px-4 lg:px-6">
      {/* Below 768px the sidebar is a drawer and this is the only way into it;
          at desktop width the sidebar is always on screen, so it is not. */}
      <RoundButton label="Open navigation" onClick={onOpenNav} className="md:hidden">
        <Menu className="size-[19px]" aria-hidden />
      </RoundButton>

      <p className="hidden min-w-0 truncate text-h2 text-ink sm:block">
        {greetingFor(new Date())}
        {firstName ? `, ${firstName}!` : '!'}
      </p>

      <div className="flex flex-1 justify-end lg:justify-center">
        <button
          type="button"
          onClick={onOpenSearch}
          className={cn(
            'hidden h-[46px] w-full max-w-[420px] items-center gap-3 rounded-full border border-line bg-hull px-5',
            'text-body text-ink-faint transition-colors duration-fast ease-std hover:border-line-strong lg:flex',
          )}
        >
          <Search className="size-[18px] shrink-0" aria-hidden />
          <span className="flex-1 truncate text-start">Search clients, yachts, bookings…</span>
          <kbd className="shrink-0 rounded-pill border border-line px-2 text-micro text-ink-faint">⌘K</kbd>
        </button>
      </div>

      <div className="flex shrink-0 items-center gap-2 lg:gap-3">
        <RoundButton label="Search" onClick={onOpenSearch} className="lg:hidden">
          <Search className="size-[19px]" aria-hidden />
        </RoundButton>

        <RoundButton
          label={chrome.theme === 'navy' ? 'Switch to light chrome' : 'Switch to navy chrome'}
          onClick={() => router.post(`/me/chrome/${chrome.theme === 'navy' ? 'light' : 'navy'}`)}
        >
          {chrome.theme === 'navy' ? <Sun className="size-[19px]" aria-hidden /> : <Moon className="size-[19px]" aria-hidden />}
        </RoundButton>

        <RoundButton label="Alerts" href="/notifications">
          <Bell className="size-[19px]" aria-hidden />
          <span className="absolute end-[10px] top-[10px] size-2 rounded-full bg-danger ring-2 ring-hull" aria-hidden />
        </RoundButton>

        <Link href="/me/profile" className="shrink-0 rounded-full" aria-label="Profile and security">
          <Avatar name={user?.name} src={user?.avatar_url} size="lg" />
        </Link>
      </div>
    </header>
  )
}
