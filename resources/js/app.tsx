import '../css/app.css'

import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createRoot } from 'react-dom/client'
import type { ComponentType, ReactElement } from 'react'
import { AppLayout } from '@/layouts/AppLayout'

const appName = import.meta.env.VITE_APP_NAME || 'Walidia Yachts'

type PageComponent = ComponentType<Record<string, unknown>> & {
  layout?: ((page: ReactElement) => ReactElement) | undefined
}

interface PageModule {
  default: PageComponent
}

void createInertiaApp({
  title: (title) => (title ? `${title} · ${appName}` : appName),

  resolve: async (name) => {
    const page = await resolvePageComponent<PageModule>(
      `./pages/${name}.tsx`,
      import.meta.glob<PageModule>('./pages/**/*.tsx'),
    )

    // Pages opt out of the shell by exporting `layout = undefined` (auth
    // screens do). Everything else gets it without repeating it per page.
    if (!('layout' in page.default)) {
      page.default.layout = (pageElement: ReactElement) => <AppLayout>{pageElement}</AppLayout>
    }

    return page.default
  },

  setup({ el, App, props }) {
    if (!el) return
    createRoot(el).render(<App {...props} />)
  },

  progress: { color: '#B8894A', showSpinner: false },
})
