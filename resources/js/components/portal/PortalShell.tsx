import type { ReactNode } from 'react'
import { Head } from '@inertiajs/react'
import { DateText } from '@/ui/Primitives'

/**
 * The frame a portal page sits in.
 *
 * Deliberately not the application shell: no sidebar, no search, no account
 * menu, nothing to click through to. A recipient holds a key to one document,
 * and the page should look like exactly that.
 */
export function PortalShell({
  title,
  eyebrow,
  expiresAt,
  children,
}: {
  title: string
  eyebrow?: string | null
  expiresAt?: string | null
  children: ReactNode
}) {
  return (
    <div className="min-h-screen bg-deck">
      <Head title={title} />

      <header className="border-b border-line bg-hull">
        <div className="mx-auto flex max-w-3xl flex-col gap-1 px-5 py-6">
          <span className="text-micro uppercase tracking-wide text-accent">Walidia Yachts</span>
          {eyebrow && <span className="text-small text-ink-faint">{eyebrow}</span>}
          <h1 className="text-h1 text-ink">{title}</h1>
        </div>
      </header>

      <main className="mx-auto flex max-w-3xl flex-col gap-4 px-5 py-6">{children}</main>

      <footer className="mx-auto max-w-3xl px-5 pb-10 pt-4">
        <p className="text-small text-ink-faint">
          This is a private link, for you alone.
          {expiresAt && (
            <>
              {' '}
              It stops working on <DateText value={expiresAt} />.
            </>
          )}{' '}
          Please do not forward it.
        </p>
      </footer>
    </div>
  )
}
