/**
 * Walidia Yachts — Tailwind theme bound to the CSS token layer.
 * Phase 0 artifact; lands as tailwind.config.ts in Phase 1.
 *
 * Every value indirects through a CSS custom property so that
 * [data-chrome] / [data-accent] swaps work without a rebuild (D-009),
 * and so no component can invent a colour.
 */
import type { Config } from 'tailwindcss'

const config: Config = {
  content: ['./resources/js/**/*.{ts,tsx}', './resources/views/**/*.blade.php'],
  theme: {
    // Replace, not extend — the scales below are the only ones permitted.
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
      accent: {
        DEFAULT: 'var(--accent)',
        hover: 'var(--accent-hover)',
        press: 'var(--accent-press)',
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
      // Status hues — the only colours allowed to carry meaning.
      success: { DEFAULT: 'var(--status-green)', bg: 'var(--status-green-bg)' },
      info: { DEFAULT: 'var(--status-blue)', bg: 'var(--status-blue-bg)' },
      warning: { DEFAULT: 'var(--status-amber)', bg: 'var(--status-amber-bg)' },
      attention: { DEFAULT: 'var(--status-orange)', bg: 'var(--status-orange-bg)' },
      danger: { DEFAULT: 'var(--status-red)', bg: 'var(--status-red-bg)' },
      neutral: { DEFAULT: 'var(--status-grey)', bg: 'var(--status-grey-bg)' },
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
      1: 'var(--space-1)',
      2: 'var(--space-2)',
      3: 'var(--space-3)',
      4: 'var(--space-4)',
      5: 'var(--space-5)',
      6: 'var(--space-6)',
      8: 'var(--space-8)',
      10: 'var(--space-10)',
      12: 'var(--space-12)',
      px: '1px',
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
      sticky: 'var(--z-sticky)',
      drawer: 'var(--z-drawer)',
      popover: 'var(--z-popover)',
      modal: 'var(--z-modal)',
      toast: 'var(--z-toast)',
    },

    extend: {
      transitionTimingFunction: { std: 'var(--ease)' },
      transitionDuration: { fast: 'var(--dur-fast)', base: 'var(--dur-base)', slow: 'var(--dur-slow)' },
    },
  },

  // RTL is a first-commit requirement (D-012): logical utilities only.
  // The ESLint rule `walidia/no-physical-spacing` bans ml-/mr-/pl-/pr-/left-/right-.
  plugins: [require('@tailwindcss/forms')({ strategy: 'class' })],
}

export default config
