import '../css/app.css'

import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createRoot } from 'react-dom/client'
import type { ReactElement } from 'react'
import { AppLayout } from '@/layouts/AppLayout'

const appName = import.meta.env.VITE_APP_NAME || 'Walidia Yachts'

void createInertiaApp({
  title: (title) => (title ? `${title} · ${appName}` : appName),
  resolve: async (name) => {
    const page = await resolvePageComponent(
      `./pages/${name}.tsx`,
      import.meta.glob('./pages/**/*.tsx'),
    )
    const component = page.default as { layout?: (page: ReactElement) => ReactElement }

    // Auth screens opt out by exporting `layout = undefined`; everything else
    // gets the shell without repeating it on every page.
    if (component.layout === undefined && !name.startsWith('Auth/')) {
      component.layout = (pageElement: ReactElement) => <AppLayout>{pageElement}</AppLayout>
    }

    return page
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
  progress: { color: 'var(--accent)', showSpinner: false },
})
