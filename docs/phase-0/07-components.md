# 07 · Component Inventory

Built in Phase 1, on Radix primitives, styled with the token layer. Every component in this list appears in the shared UI reference or is required by a screen in the brief. Nothing here is speculative, and nothing outside this list gets invented in a feature branch without being added here first.

Storybook-equivalent: a `/dev/ui` route (local only) rendering every component in every state, in both chrome themes and both directions.

---

## 1 · Shell

| Component | Radix base | Notes |
|---|---|---|
| `AppShell` | — | Grid: sidebar + (topbar / content). Owns `data-chrome`, `dir`, and the responsive mode |
| `Sidebar` | — | 260px → 64px rail (768–1023px) → `Dialog` drawer (<768px) |
| `WorkspaceSwitcher` | `DropdownMenu` | Company + business line; switching filters the whole shell |
| `SidebarNav` / `NavItem` / `NavGroup` | `Collapsible` | Role-filtered from one tree; active state by route match; badge slot for counts |
| `SidebarUserCard` | `DropdownMenu` | Profile, 2FA state, EN/AR toggle, sessions, sign out |
| `Topbar` | — | Breadcrumb + `GlobalSearch` + `QuickCreate` + `NotificationBell` + user chip |
| `GlobalSearch` | `Dialog` + `Command` | ⌘K palette; grouped results; recent; keyboard-first |
| `PageHeader` | — | h1 + description + primary CTA + secondary actions |
| `Toolbar` | — | Search field, `FilterBar`, Export ▾, Import ▾, primary CTA — the reference's second row |
| `BottomActionBar` | — | <768px only; holds the primary CTA and the save action |

## 2 · Primitives

`Button` (primary / secondary / ghost / destructive / link × sm, md, lg; `loading` disables and swaps to a spinner without a width jump) · `IconButton` (always `aria-label`ed) · `SplitButton` · `Input` · `Textarea` · `Select` · `Combobox` (async, used for client, yacht and vendor pickers) · `MultiSelect` · `Checkbox` · `RadioGroup` · `Switch` · `DatePicker` / `DateRangePicker` (date-fns, marina timezone aware) · `TimePicker` · `MoneyInput` (currency prefix, tabular, integer-minor-unit binding) · `PhoneInput` (E.164, UAE default) · `FileDrop` (client-side type/size pre-check mirroring the server rules) · `Label` · `FieldError` · `HelpText` · `Tooltip` · `Popover` · `DropdownMenu` · `Tabs` · `Accordion` · `Avatar` · `Badge` · `Separator` · `Skeleton` · `ProgressBar` · `Spinner`.

## 3 · Data display

### `DataTable` (TanStack Table v8)
The workhorse — it is the reference screenshot's table.

```ts
type Column<T> = {
  id: string
  header: string
  cell: (row: T) => ReactNode
  align?: 'start' | 'end'          // 'end' for every numeric column
  sortable?: boolean
  priority: 1 | 2 | 3              // 1 never drops; 3 drops first (§5 of 01-design-system)
  width?: number | 'auto'
  numeric?: boolean                // applies tabular-nums
}
```
Server-driven sort, filter and pagination (Inertia partial reloads). Features: sticky header, sticky identity column on mobile, row selection with a `BulkBar`, per-user column visibility and order persisted to `user_preferences`, `RowActions` at the end of every row, keyboard navigation, and a `MobileRecordCard` renderer below 768px.

| Component | Notes |
|---|---|
| `ColumnHeader` | Label + sort affordance, matching the reference's paired chevrons |
| `IdentityCell` | Avatar + name + subtitle — the reference's first column |
| `StatusPill` | `status` token → dot + label, 12px/500, 4px radius. Never a bare dot |
| `OverflowChips` | `ACE Homes LLC +2` — the rest in a popover |
| `RowActions` | Two quick-action `IconButton`s (call, WhatsApp) + `…` `DropdownMenu` with a `MENU` header, and the destructive item last in `--danger` |
| `BulkBar` | Appears on selection: count, Assign, Status, Tag, Export, Archive, Clear |
| `Pagination` | `‹ Previous`, numbered pages with ellipsis, `Next ›`, page-size select |
| `EmptyState` | Icon, one-line explanation of what belongs here, and the primary action — never a bare "No data" |
| `ErrorState` / `LoadingState` | Skeleton rows matching the real row height, so nothing reflows |

### Other data components

| Component | Used by |
|---|---|
| `MetricCard` | Dashboard and every module header — icon tile in a status hue, label, `…` menu, display-size tabular value, delta with a labelled comparison period |
| `KanbanBoard` / `KanbanCard` | The three pipelines; drag fires the gate evaluator and snaps back on block |
| `Timeline` / `TimelineItem` | The polymorphic activity feed, grouped by day, filterable by type |
| `DetailPane` | Split-pane record layout: header, key facts, tabs, timeline |
| `DrawerForm` | Create/edit on desktop; becomes a full page below 768px |
| `Modal` / `ConfirmDialog` | Destructive actions; the archive confirmation names the record |
| `Toast` | Save, queue, and error confirmations; never used for validation errors, which belong on the field |
| `FilterBar` / `FilterChip` / `SavedViewMenu` | Every index screen |
| `DocumentList` / `DocumentCard` | Vault, with version, expiry and signature state |
| `ChecklistRow` | 44px+ tap target, photo capture, signature, blocking indicator |
| `SignaturePad` | Fallback signing page and on-dock acceptance |
| `Money` / `DateText` / `Num` | The only sanctioned way to render a figure — they apply tabular numerals and locale formatting so no cell can forget |

## 4 · Gate engine components

| Component | Behaviour |
|---|---|
| `GateButton` | Wraps any guarded action. Reads a `GateResult`, renders enabled / warned / blocked, and never lets a blocked click through |
| `GateFailurePanel` | The inline explanation: one line per failed condition, each with its resolution link (see `06-gate-engine.md` §4) |
| `GateWarningNote` | Amber inline note for soft gates |
| `OverrideDialog` | Failed conditions read-only, mandatory reason (min 20 chars), risk acknowledgement, Admin only |
| `BlockerList` | The Alerts & Blockers dashboard panel, grouped by severity |

## 5 · Rules that apply to all of them

- **No raw colour.** Tokens only; enforced by an ESLint rule banning hex in `.tsx`.
- **No physical spacing.** `ms-/me-/ps-/pe-` only, so RTL is free (D-012).
- **Every interactive element** has a visible `--accent-ring` focus state, an accessible name, and a 44px tap target below 768px.
- **Every form** is React Hook Form + Zod, with the Zod schema asserted against the Laravel Form Request rules in a test — the server stays the source of truth.
- **Every list** ships its empty, loading, error and permission-denied states in the same PR as the happy path.
- **Every destructive action** confirms, names the record, and is reversible from Archive.
- **Nothing animates on mount**; motion answers an action (§4 of `01-design-system.md`).
