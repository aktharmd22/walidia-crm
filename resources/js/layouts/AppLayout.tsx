import { useEffect, useState, type ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import * as Dialog from '@radix-ui/react-dialog'
import { Sidebar } from '@/components/shell/Sidebar'
import { Topbar, type Crumb } from '@/components/shell/Topbar'
import { TooltipProvider } from '@/ui/Overlays'
import { Toaster } from '@/components/shell/Toaster'
import { GlobalSearch } from '@/components/shell/GlobalSearch'
import type { SharedProps } from '@/types'

export interface AppLayoutProps {
  children: ReactNode
  crumbs?: Crumb[]
}

/**
 * The shell: fixed sidebar ≥1024px, icon rail 768–1023px, slide-over drawer
 * below 768px. Chrome and direction come from the server so the first paint
 * is already correct.
 */
export function AppLayout({ children, crumbs = [] }: AppLayoutProps) {
  const { props } = usePage<SharedProps>()
  const [navOpen, setNavOpen] = useState(false)
  const [searchOpen, setSearchOpen] = useState(false)

  useEffect(() => {
    const root = document.documentElement
    root.setAttribute('data-chrome', props.chrome.theme)
    root.setAttribute('data-accent', props.chrome.accent)
    root.setAttribute('dir', props.direction)
    root.setAttribute('lang', props.locale)
  }, [props.chrome.theme, props.chrome.accent, props.direction, props.locale])

  useEffect(() => {
    function onKey(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault()
        setSearchOpen(true)
      }
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [])

  return (
    <TooltipProvider>
      <div className="flex min-h-screen bg-deck">
        {/* ≥768px: persistent sidebar, collapsed to a rail below lg */}
        <div className="sticky top-0 hidden h-screen shrink-0 md:block">
          <div className="hidden h-full lg:block">
            <Sidebar />
          </div>
          <div className="h-full lg:hidden">
            <Sidebar collapsed />
          </div>
        </div>

        {/* <768px: slide-over drawer */}
        <Dialog.Root open={navOpen} onOpenChange={setNavOpen}>
          <Dialog.Portal>
            <Dialog.Overlay className="fixed inset-0 z-drawer bg-ink/40 md:hidden" />
            <Dialog.Content className="fixed inset-y-0 start-0 z-drawer w-sidebar md:hidden focus:outline-none">
              <Dialog.Title className="sr-only">Navigation</Dialog.Title>
              <Sidebar mobile onClose={() => setNavOpen(false)} />
            </Dialog.Content>
          </Dialog.Portal>
        </Dialog.Root>

        <div className="flex min-w-0 flex-1 flex-col">
          <Topbar crumbs={crumbs} onOpenNav={() => setNavOpen(true)} onOpenSearch={() => setSearchOpen(true)} />
          <main className="flex-1 px-4 py-5 lg:px-6 lg:py-6">
            <div className="mx-auto flex w-full max-w-[1440px] flex-col gap-5">{children}</div>
          </main>
        </div>
      </div>

      <GlobalSearch open={searchOpen} onOpenChange={setSearchOpen} />
      <Toaster />
    </TooltipProvider>
  )
}
