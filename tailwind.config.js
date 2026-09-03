import forms from '@tailwindcss/forms'

/**
 * Walidia Yachts — Tailwind theme bound to the CSS token layer.
 * Every value indirects through a CSS custom property so [data-chrome]
 * and [data-accent] swaps work without a rebuild (D-009).
 *
 * RTL: logical utilities only (ms-/me-/ps-/pe-/start-/end-) — D-012.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.{js,ts,jsx,tsx}',
  ],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      ink: {
        DEFAULT: 'var(--ink)',
        soft: 'var(--ink-soft)',
        faint: 'var(--ink-faint)',
      },
      hull: 'var(--hull)',
      deck: 'var(--deck)',
      line: { DEFAULT: 'var(--line)', strong: 'var(--line-strong)' },
      whatsapp: { DEFAULT: 'var(--whatsapp)', soft: 'var(--whatsapp-soft)' },

      accent: {
        DEFAULT: 'var(--accent)',
        hover: 'var(--accent-hover)',
        press: 'var(--accent-press)',
        ink: 'var(--accent-ink)',
        soft: 'var(--accent-soft)',
        on: 'var(--accent-on)',
      },
      sidebar: {
        bg: 'var(--sidebar-bg)',
        fg: 'var(--sidebar-fg)',
        muted: 'var(--sidebar-fg-muted)',
        active: 'var(--sidebar-fg-active)',
        'active-bg': 'var(--sidebar-active-bg)',
        'hover-bg': 'var(--sidebar-hover-bg)',
        line: 'var(--sidebar-line)',
      },
      success: { DEFAULT: 'var(--success)', bg: 'var(--success-bg)' },
      info: { DEFAULT: 'var(--info)', bg: 'var(--info-bg)' },
      warning: { DEFAULT: 'var(--warning)', bg: 'var(--warning-bg)' },
      attention: { DEFAULT: 'var(--attention)', bg: 'var(--attention-bg)' },
      danger: { DEFAULT: 'var(--danger)', bg: 'var(--danger-bg)' },
      neutral: { DEFAULT: 'var(--neutral)', bg: 'var(--neutral-bg)' },
      white: '#FFFFFF',
    },

    fontFamily: { sans: 'var(--font-sans)' },

    fontSize: {
      display: ['var(--text-display)', { lineHeight: 'var(--leading-display)', letterSpacing: 'var(--tracking-display)', fontWeight: '500' }],
      h1: ['var(--text-h1)', { lineHeight: 'var(--leading-h1)', letterSpacing: 'var(--tracking-h1)', fontWeight: '500' }],
      h2: ['var(--text-h2)', { lineHeight: 'var(--leading-h2)', letterSpacing: 'var(--tracking-h2)', fontWeight: '500' }],
      h3: ['var(--text-h3)', { lineHeight: 'var(--leading-h3)', fontWeight: '500' }],
      body: ['var(--text-body)', { lineHeight: 'var(--leading-body)' }],
      small: ['var(--text-small)', { lineHeight: 'var(--leading-small)' }],
      micro: ['var(--text-micro)', { lineHeight: 'var(--leading-micro)', letterSpacing: 'var(--tracking-micro)', fontWeight: '500' }],
    },

    spacing: {
      0: '0px',
      px: '1px',
      1: 'var(--space-1)',
      2: 'var(--space-2)',
      3: 'var(--space-3)',
      4: 'var(--space-4)',
      5: 'var(--space-5)',
      6: 'var(--space-6)',
      8: 'var(--space-8)',
      10: 'var(--space-10)',
      12: 'var(--space-12)',
      row: 'var(--row-h)',
      'row-rich': 'var(--row-h-rich)',
      field: 'var(--field-h)',
      topbar: 'var(--topbar-h)',
      sidebar: 'var(--sidebar-width)',
      rail: 'var(--sidebar-rail)',
    },

    borderRadius: {
      none: '0',
      pill: 'var(--radius-pill)',
      card: 'var(--radius-card)',
      shell: 'var(--radius-shell)',
      full: 'var(--radius-full)',
    },

    boxShadow: {
      none: 'none',
      card: 'var(--shadow-card)',
      pop: 'var(--shadow-pop)',
      modal: 'var(--shadow-modal)',
      toast: 'var(--shadow-toast)',
    },

    screens: {
      sm: '480px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      '2xl': '1536px',
    },

    zIndex: {
      0: '0',
      sticky: 'var(--z-sticky)',
      drawer: 'var(--z-drawer)',
      popover: 'var(--z-popover)',
      modal: 'var(--z-modal)',
      toast: 'var(--z-toast)',
    },

    extend: {
      transitionTimingFunction: { std: 'var(--ease)' },
      transitionDuration: { fast: 'var(--dur-fast)', base: 'var(--dur-base)', slow: 'var(--dur-slow)' },
      minHeight: { row: 'var(--row-h)', field: 'var(--field-h)' },
      maxWidth: { prose: '68ch' },
    },
  },
  plugins: [forms({ strategy: 'class' })],
}
