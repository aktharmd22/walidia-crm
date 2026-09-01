# 01 · Design System

Two inputs govern the interface: the written direction in the master prompt (DM Sans, navy sidebar, brass accent, borders over shadows, working-tool density) and the UI reference screenshot the client shared (a light sidebar CRM with a blue accent, KPI cards, tabs, a dense data table with row actions, and pagination).

**They agree on structure and disagree on chrome.** The resolution below adopts the reference screenshot's *layout anatomy and component set* verbatim, and keeps the brief's *brand chrome* as the default — with the disagreement isolated to a handful of tokens so switching is one attribute, not a re-skin. See Q1 in `08-open-questions.md`.

---

## 1. Screen anatomy — taken from the shared reference

```
┌───────────────────────┬──────────────────────────────────────────────────────────┐
│ ① workspace switcher  │ ⑤ breadcrumb / page title      search ⌘K  ⚡  🔔  user ▾ │
│    Walidia Yachts ▾   ├──────────────────────────────────────────────────────────┤
│                       │ ⑥ toolbar                                                │
│ ② section label       │   [ search…                    ]  Filter  Export ▾       │
│    MENU               │                                   Import ▾  [+ Primary ] │
│    ▸ Dashboard        ├──────────────────────────────────────────────────────────┤
│    ▸ Leads            │ ⑦ metric row — 3–4 cards                                 │
│    ▪ Charter (active) │   ┌────────────┐ ┌────────────┐ ┌────────────┐          │
│    ▸ Brokerage        │   │▣ Label   … │ │▣ Label   … │ │▣ Label   … │          │
│    ▸ …                │   │  128       │ │  42        │ │  9.8       │          │
│                       │   │  ▲+12% vs… │ │  ▲+12% vs… │ │  ▲+12% vs… │          │
│                       │   └────────────┘ └────────────┘ └────────────┘          │
│                       ├──────────────────────────────────────────────────────────┤
│                       │ ⑧ scope tabs   [ All Bookings ] [ Mine ]                 │
│                       │ ⑨ data table                                             │
│                       │   NAME ⇅   STATUS ⇅   YACHT ⇅   VALUE ⇅   DATE ⇅   ⋯    │
│                       │   ● row …                                    ☎ ✉ ⋯       │
│                       │                                              ┌─────────┐ │
│                       │                                              │ MENU    │ │
│ ④ user card           │ ⑩ ‹ Previous  1 2 3 4 5 … 15  Next ›         │ Details │ │
│    Arthur Taylor ✓  › │                                              │ Edit    │ │
└───────────────────────┴──────────────────────────────────────────────┴─────────┘─┘
```

| # | Element | Spec | Walidia mapping |
|---|---|---|---|
| ① | Workspace switcher | 260px wide, 56px tall, avatar 32px + name (h3) + role (micro, muted) + chevron | Company + active business line (Charter / Brokerage / Management), which filters the whole shell |
| ② | Section label | micro, uppercase-free, `--sidebar-fg-muted`, 20px above group | `MENU`, `OPERATIONS`, `ADMIN` groups from the nav tree |
| ③ | Nav item | 36px tall, 6px radius, 16px Lucide icon + body label, 10px gap; active = filled row + `--sidebar-active-fg`; parents expand to a child list, no flyout on desktop | The nav tree in §8 of the brief, filtered by role |
| ④ | User card | pinned bottom, bordered top, avatar + name + email (small, muted) + chevron → account menu | Session, 2FA status, language toggle EN/AR, sign out, active sessions |
| ⑤ | Top bar | 56px, breadcrumb left; right cluster: global search with ⌘K hint, quick-create ⚡, alerts 🔔 with dot, user chip | Global search across clients/yachts/bookings/listings; alerts = gate blockers and expiries |
| ⑥ | Toolbar | one row: left search field (max 420px), right: Filter, Export ▾, Import ▾, primary CTA | Per-module. CTA is always the single most likely creation action |
| ⑦ | Metric cards | equal-width grid, 1px border, 6px radius, 20px pad; 36px rounded icon tile in a status hue; label (h3), `…` menu, display number (tabular), delta line | Three per screen max. Deltas are period-over-period and always carry the comparison label |
| ⑧ | Scope tabs | segmented control, 8px radius container, active tab white on `--deck`, icon + label | `All` vs `Mine` on every list, plus module-specific scopes (e.g. Upcoming / Past) |
| ⑨ | Data table | card-wrapped; header 40px, `--deck` fill, micro uppercase-free labels with a sort affordance; rows 44px, 1px `--line` separators; first column identity (avatar + name); status as a dot-and-label pill; overflow chips (`+2`); sticky first column on mobile | See `07-components.md` |
| ⑩ | Pagination | left `‹ Previous`, centre numbered pages with ellipsis, right `Next ›`; page-size selector added on the left | Server-driven cursor or offset paging, 25/50/100 |
| — | Row menu | 200px popover, `MENU` micro header, 36px items with 16px icons, destructive item in `--status-red`, 8px radius, the only place shadow is used | `Details · Edit · <domain action> · Archive` — see the CRUD conventions in `04-routes.md` |

**Adopted from the reference and kept:** the two-row header (top bar + toolbar), the icon-tile metric card, the segmented scope tabs, the identity-first table row, the trailing quick-action icons plus `…` menu, the overflow chip, and the pagination bar.

**Deliberately not adopted:** the soft drop shadow under cards (the brief calls for borders; shadow is reserved for genuinely floating layers), and the 12px pill radius (4px per the brief, which reads more instrument than consumer).

---

## 2. Typography — DM Sans, self-hosted, one family

Variable DM Sans, latin + latin-ext, `font-display: swap`, preloaded, served from the app origin (never Google Fonts, per the CSP in §4 of the brief). Arabic falls back to a self-hosted **IBM Plex Sans Arabic** for `[lang="ar"]` — DM Sans has no Arabic coverage, and this is the closest geometric-humanist match at the same optical weight. (Confirm in Q19.)

| Token | Size / line | Weight | Tracking | Used for |
|---|---|---|---|---|
| `display` | 32 / 40 | 500 | −0.02em | Metric card values, empty-state headline |
| `h1` | 24 / 32 | 500 | −0.015em | Page title |
| `h2` | 18 / 26 | 500 | −0.01em | Card and section titles, drawer titles |
| `h3` | 15 / 22 | 500 | — | Table identity cell, nav item, field group label |
| `body` | 14 / 22 | 400 | — | Default |
| `small` | 13 / 20 | 400 | — | Secondary cell text, helper text |
| `micro` | 12 / 16 | 500 | 0.01em | Column headers, pills, menu section labels, badges |
| `numeric` | inherits | 500 | — | `font-variant-numeric: tabular-nums` |

`numeric` is mandatory on every AED figure, percentage, date, count, booking ID and yacht ID — applied by the `<Money>`, `<DateText>` and `<Num>` primitives so it cannot be forgotten in a cell.

---

## 3. Colour

Brand and surface:

| Token | Value | Use |
|---|---|---|
| `--ink` | `#0F1B2D` | Primary text; navy sidebar fill |
| `--ink-soft` | `#46566B` | Secondary text |
| `--ink-faint` | `#8A97A8` | Tertiary text, placeholders, disabled |
| `--hull` | `#FFFFFF` | Card and input surface |
| `--deck` | `#F6F8FA` | App background, table header, inactive tab track |
| `--line` | `#E3E8EF` | Borders and dividers |
| `--brass` | `#B8894A` | Accent: primary button, active nav, focus ring, links |
| `--brass-soft` | `#FDF6EC` | Accent wash: active nav in light chrome, selected row |

Status — one hue per meaning, used identically in pills, chart series, calendar blocks and metric icon tiles:

| Meaning | Text / icon | 10% tint |
|---|---|---|
| completed · sold · paid · closed won | `#1B7F4B` | `#E8F2ED` |
| active · in progress · confirmed | `#2563A8` | `#E9EFF6` |
| pending action · follow-up · awaiting | `#B87B12` | `#F8F2E7` |
| offer received · under review · negotiation | `#C2621C` | `#F9EFE8` |
| closed lost · cancelled · rejected · overdue · expired | `#B4232A` | `#F8E9EA` |
| draft · on hold · off market · archived | `#6B7A8D` | `#F0F2F4` |

Pill: 12px / weight 500, 4px radius, 6px×2px padding, a 6px leading dot in the same hue, label always present. A status hue never appears as decoration — if it is coloured, it means something.

**Chrome themes** (D-009). The shell reads `--sidebar-*` and `--accent`; two presets exist:

- `data-chrome="navy"` (default) — sidebar `#0F1B2D`, sidebar text `#C7D0DC`, active row `rgba(184,137,74,.16)` with white text, accent brass.
- `data-chrome="light"` — sidebar `#FFFFFF` with a `--line` end-border, sidebar text `--ink-soft`, active row `--brass-soft` with `--ink`, matching the reference screenshot. Pairing it with `--accent: #2563A8` reproduces the reference almost exactly.

---

## 4. Space, radius, elevation, motion

- **Spacing scale:** 4 · 8 · 12 · 16 · 20 · 24 · 32 · 40 · 48. Nothing else.
- **Radius:** 6px cards, inputs, drawers, popovers; 4px pills, buttons, chips, badges; 8px tab track and modal; 999px only on avatars.
- **Elevation:** borders carry the hierarchy. Exactly three shadows exist — `--shadow-pop` (dropdowns, popovers, comboboxes), `--shadow-modal` (modals, drawers), `--shadow-toast`. Cards, tables and metric tiles use `1px solid var(--line)` and nothing else.
- **Density:** table rows 44px (52px when the identity cell carries an avatar and a subtitle), form fields 36px, buttons 36px (32px small, 40px on mobile primary), 12px vertical rhythm inside a form, 20px card padding, 24px page gutter (16px below 768px).
- **Motion:** 120ms for hover and focus, 180ms `cubic-bezier(.2,0,0,1)` for drawers, popovers and toasts, 220ms for the sidebar drawer. Nothing animates on page load. Everything respects `prefers-reduced-motion: reduce`, which disables transforms and keeps opacity only.
- **Focus:** `outline: 2px solid var(--accent-ring); outline-offset: 2px` on every interactive element, never removed, visible on keyboard and on programmatic focus after a drawer opens.

---

## 5. Responsive behaviour

| Breakpoint | Shell | Tables | Detail pages |
|---|---|---|---|
| ≥1280px | Sidebar fixed 260px | All columns, sticky header, inline row actions | Split pane: record on the left, timeline/panel on the right |
| 1024–1279px | Sidebar 260px, toolbar wraps to two rows | Lower-priority columns drop by declared `priority` | Stacked, right panel becomes a tab |
| 768–1023px | Sidebar collapses to a 64px icon rail with tooltips | Identity + status + one metric + actions | Stacked, sticky sub-nav |
| <768px | Sidebar becomes a slide-over drawer; a bottom action bar carries the primary CTA | Rows become stacked cards: identity line, status pill, two key facts, action row | Single column, sectioned accordions, sticky save bar |

Every column declares a `priority` (1 = never drop) so column shedding is data, not media-query guesswork. Tap targets are 44px minimum below 768px. Tested at 375 / 768 / 1024 / 1440.

**Mobile-first by construction:** the Charter Day screen and the Operations Checklist are designed at 375px first and inherit upward. Both assume one thumb, sunlight, and a poor connection: large targets, optimistic UI with a queued-write indicator, and no action that requires a modal on top of a modal. Offline capability is Q26.

---

## 6. Localisation

`dir` is set on `<html>` from the user locale. Only logical Tailwind utilities are permitted (D-012). Layout, tables, charts, the sidebar, drawers and toasts all mirror; directional icons mirror through `DirectionalIcon`; charts flip axis placement. Currency renders as `AED 125,000.00` in English and `١٢٥٬٠٠٠٫٠٠ د.إ` only if the client wants Arabic-Indic numerals (Q19 — the proposal is Latin numerals in both locales, because finance staff cross-reference bank statements).

---

## 7. The look this must not have

No identical rounded cards floating on soft grey shadows in a three-column grid; no gradient decoration; no ALL-CAPS tracked-out eyebrow labels; no arrows glued onto button labels; no monospace for data labels; no fade-and-slide-up on section entry. Motion answers an action or it does not exist.
