# DECISIONS.md — Walidia Yachts Platform

Architectural decisions taken beyond what the master prompt specifies. Append-only; supersede an entry by adding a new one that references it.

---

### D-001 · Laravel 11 + Inertia + React, one deployable
Specified by the brief; recorded so it is not relitigated. Session auth, server-side policies, no CORS, no token storage.
**Consequence:** no public REST API in Phases 1–6. When the WordPress sync and the portals need one (Phases 6–7) it is a separate, narrowly scoped, signed-token API — not a general-purpose API over the whole CRM.

### D-002 · Money is stored as `decimal(15,2)` and computed in integer minor units
Every money column is `decimal(15,2)` with a sibling `*_currency` char(3) where currency can vary (brokerage), or an implicit AED where it cannot. In PHP, money loads into a `Money` value object backed by an integer number of fils; all arithmetic — VAT, commission percentages, splits, rounding — happens in integers, never floats.
**Why:** a cost sheet with twenty lines, 5% VAT, a percentage commission and a bank charge cannot tolerate float drift; the AED figures here run to seven digits.
**Consequence:** rounding is half-up to two decimals, applied once per line and never on running totals. A `MoneyCast` on every model. Q4 and Q5 must be answered before the cost sheet is coded.

### D-003 · One `yachts` table with capability flags, not subtype tables
`is_charter_fleet`, `is_for_sale`, `is_managed` are booleans on one record; domain attributes live in `yacht_charter_profiles`, `yacht_sale_profiles`, `yacht_management_profiles`.
**Why:** the same hull is routinely chartered, listed for sale and managed at once. Duplicating it across three tables guarantees drift in specs, photos and availability.
**Consequence:** availability, documents and media belong to the yacht, not the business line. Asking price lives on the sale profile, charter rates on the charter profile.

### D-004 · The gate engine is data, not code branches
`gate_rules` rows hold a trigger, an ordered list of condition references, a severity and a resolution link. A `GateEvaluator` resolves each condition key against a registry of small `GateCheck` classes. Rules are seeded, editable by Admin, and versioned through the audit log.
**Why:** the central promise of the brief ("what unlocks what") is a rule set that will change. Scattering it through controllers makes it unauditable and untestable.
**Consequence:** adding a new *kind* of check is a code change (a new `GateCheck`); adding, reordering, retargeting or disabling a rule is a data change. Every check class ships with its own feature test.

### D-005 · Pipeline stage is a first-class column, separate from lifecycle status
A booking has `status` (draft → confirmed → completed → cancelled) and its deal has `stage` (the board column). Stage transitions are what the gate engine guards; status is updated by listeners.
**Why:** "Deposit Paid" is a pipeline position; "confirmed" is a fact about the booking. Conflating them makes the board undraggable without side effects.
**Consequence:** one `deals` table backs all three pipelines via `pipeline_id`, so the board, the gates and pipeline reporting are written once rather than three times.

### D-006 · Polymorphic timeline, documents, tasks and notes
`activities`, `documents`, `tasks`, `notes` and `attachments` attach to any subject via `subject_type`/`subject_id`, indexed on the pair.
**Why:** a call belongs to a lead today and a booking tomorrow; the client 360° timeline must union all of it cheaply.
**Consequence:** a `HasTimeline` trait. A denormalised projection only if index-page profiling demands it — not before.

### D-007 · Encrypted fields get a blind index for lookup
Passport, EID, TRN and bank fields use Laravel `encrypted` casts. Where staff must search them, a sibling `*_hash` column stores an HMAC-SHA256 of the normalised value under a separate key, indexed and used for exact-match lookup only.
**Why:** encrypted columns cannot be queried, but "find the client holding this passport" is a real duplicate-check workflow.
**Consequence:** no partial search on those fields, by design. Key rotation needs a re-index job, written in Phase 2 alongside the fields.

### D-008 · Soft deletes and auditing everywhere; "Delete" in the UI means archive
No hard deletes from business tables. The row-action Delete performs a soft delete, is permission-gated, requires confirmation, and is reversible from an Archive view.
**Why:** the premise of the brief — the cost of a lost record is enormous.
**Consequence:** unique constraints are composite with `deleted_at` where a value must be reusable. A hard purge exists only as a documented Admin console command for erasure requests.

### D-009 · Sidebar chrome and accent are tokenised, so the UI question is one variable set
`--sidebar-*` tokens are consumed by the shell; `data-chrome="navy"` (default, per the brand direction) and `data-chrome="light"` (per the shared UI reference) swap them, as do `--accent` brass and blue.
**Why:** the brief specifies a navy sidebar with a brass accent; the reference screenshot is a light sidebar with a blue accent. Both are one attribute apart until the client chooses. See Q1.
**Consequence:** no component hardcodes a chrome colour; an ESLint rule bans raw hex in `.tsx`.

### D-010 · Store UTC, display `Asia/Dubai`, keep charter times timezone-explicit
All timestamps are UTC in MySQL; conversion happens at the edge. Charter dates and times are stored as UTC instants derived from the departure marina timezone, never as naive local strings.
**Why:** Seychelles (+4) and the Maldives (+5) share a fleet calendar with the UAE (+4). Naive strings would corrupt utilisation reporting.
**Consequence:** `marinas` carries a `timezone` column and the booking derives its instants from the departure marina.

### D-011 · The cost sheet is one object with three phases, not three documents
`cost_sheet_lines` carry `phase ∈ {quoted, invoiced, actual}`. Quote → invoice copies lines forward; actuals overwrite only the actual phase. P&L reads actual and falls back to invoiced.
**Why:** the client's own Cost & Offer table flows quote → invoice → actuals → P&L as one artifact. Splitting it loses the variance analysis that makes it valuable.
**Consequence:** the Charter P&L screen becomes a quoted-vs-actual variance view for free.

### D-012 · RTL from the first commit via logical properties
Only `ms-/me-/ps-/pe-/start-/end-` in Tailwind; `dir` set on `<html>` from the user locale; direction-encoding icons mirrored through a `DirectionalIcon` wrapper. Numerals stay Latin in both locales unless the client asks otherwise (Q19).
**Consequence:** an ESLint rule bans `ml-/mr-/pl-/pr-/left-/right-` inside `resources/js`.

### D-013 · Human-facing identifiers come from a locking sequence service
Booking IDs, invoice, quotation, PO and receipt numbers are issued by a `SequenceService` against a row-locked `sequences` table inside the same transaction as the record.
**Why:** the FTA requires gapless sequential tax invoice numbering; `max(id)+1` under concurrency does not deliver it.
**Consequence:** formats are configurable per type in Settings; defaults proposed in Q8.

### D-014 · The frontend is organised by feature, not by file type
`resources/js/features/<domain>/{pages,components,hooks,schemas}`, with a shared `resources/js/ui` primitive library and `resources/js/layouts`. Zod schemas live beside the form and are asserted against the Laravel Form Request rules in a test.
**Why:** twenty domains in type-first folders becomes unnavigable by Phase 3.

### D-015 · Private storage only, behind a single `DocumentUrlService`
No file is addressable without a policy check. Downloads go through a controller that authorises, logs the access, then redirects to a five-minute signed S3 URL.
**Consequence:** VIP and manifest documents get an access-log entry per view, satisfying the VIP audit requirement in the brief.

### D-016 · Every integration ships with a fake driver first
WhatsApp, e-signature, payments, email, weather and website sync each get an interface, a `Fake*` driver used in local and CI, and a real adapter later. The fake records to `communications`/`webhook_events` so the flow is demonstrable before credentials exist.
**Why:** the brief requires end-to-end operation before any vendor is chosen (Q14–Q17 are open).

### D-017 · Record visibility is enforced by global scopes, not controller filtering
A `ScopedToOwner` global scope on client-owning models restricts rows to the viewer's visible set, bypassed only by `records.view-all`. Policies then authorise the action.
**Why:** the brief requires that a Sales user cannot retrieve another agent's client by guessing an ID — that must hold on every query path, including relations and search.
**Consequence:** an explicit `withoutOwnerScope()` is required in reporting queries, and is grep-auditable.

### D-018 · Every business entity exposes complete, policy-gated CRUD
Index, create, store, show, edit, update, destroy (soft) and restore exist for every entity in the ERD, except system-generated ledgers (`audits`, `gate_overrides`, `sequences`, `webhook_events`) which are read-only by design. Sub-resources — line items, checklist items, counter-offers, crew assignments — get nested CRUD under their parent.
**Why:** explicit client instruction, and it prevents the common failure where a record can be created by a workflow but never corrected by a human.
**Consequence:** four permissions per entity (`create`, `view`, `update`, `delete`) plus `restore` where relevant, a thin shared `ResourceController` base and a generator so sixty-plus CRUD sets stay consistent. Full matrix in `docs/phase-0/04-routes.md`.

### D-019 · Bulk and import/export are part of CRUD, not an afterthought
Every index screen supports multi-select with bulk assign, bulk status change, bulk export (CSV/XLSX) and bulk archive, each running through the same policy and gate checks as the single-record action, and each queued above 500 rows. Import is a two-step preview-then-commit with per-row validation and a duplicate check.
**Why:** the reference UI puts Import/Export beside the primary CTA, and the company is migrating off spreadsheets.
