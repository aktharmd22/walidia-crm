# 00 · Phase 0 Summary

No application code has been written. This folder is the plan the build will be held to.

| Deliverable | File |
|---|---|
| Design system, reconciled with the shared UI reference | `01-design-system.md` |
| Design tokens — CSS custom properties | `02-tokens.css` |
| Design tokens — Tailwind theme | `02-tokens.tailwind.ts` |
| Full ERD — 112 tables, nine domains | `03-erd.md` |
| Route map + complete CRUD coverage matrix | `04-routes.md` |
| Permission matrix, policies, visibility scopes | `05-permissions.md` |
| Gate engine architecture | `06-gate-engine.md` |
| Gate rules as seedable data — 11 hard, 6 soft | `06-gate-rules.json` |
| Component inventory | `07-components.md` |
| Open questions, each with a proposed answer | `08-open-questions.md` |
| Architectural decision log | `../../DECISIONS.md` |
| Phase tracker | `../../PROGRESS.md` |

---

## The five decisions that shape everything else

**1 · One `deals` table drives all three pipelines, separate from lifecycle status.**
A booking's `status` is a fact about the booking; a deal's `stage` is a position on a board. Keeping them apart means the board, the gate evaluation and pipeline reporting are written once instead of three times, and dragging a card can have consequences without corrupting the record underneath. (D-005)

**2 · The gate engine is data.**
Seventeen rules live in `gate_rules`, evaluated by one service against a registry of small check classes, with every evaluation — pass or fail — persisted. This is what makes "why was this charter allowed to sail" an answerable question a year later, and it is why Admin can change the rules without a deployment. (D-004)

**3 · One yacht record, three capability profiles.**
The same 40-metre hull is chartered, listed and managed at once. Three tables would guarantee three versions of its specs. (D-003)

**4 · The cost sheet is one object with quoted / invoiced / actual phases.**
The client's own Cost & Offer table already flows quote → invoice → actuals → P&L. Modelling it as one artifact makes the P&L screen a variance view for free, and money is computed in integer fils throughout. (D-002, D-011)

**5 · Visibility is a global scope, not controller filtering.**
A Sales user guessing another agent's client ID gets a 404 from every path — index, relation, search, report. Permissions and policies then sit on top. (D-017)

---

## Where I would change the brief

**A · The UI reference and the written design direction disagree.** The brief specifies a navy sidebar, brass accent and borders instead of shadows; the screenshot shared afterwards is a light sidebar with a blue accent and soft card shadows. I have adopted the reference's *structure* wholesale — two-row header, icon-tile metric cards, segmented scope tabs, identity-first rows, quick actions plus `…` menu, pagination bar — and kept the brief's chrome as the default, with the alternative one HTML attribute away. It is a fifteen-minute switch in Phase 1 and an expensive one in Phase 5, so it should be settled at the Phase 1 walkthrough. (Q1)

**B · Hiding dietary and allergy data behind `view-vip` is an operational hazard.** A charter cannot be catered by staff who cannot read the allergy list. My proposal is a time-boxed, logged grant to the assigned Operations manager for the booking window rather than a blanket permission — but this is a safety matter and the client should decide it explicitly. (Q3)

**C · The Phase 7 "workflow builder" is a product in itself.** A general trigger-condition-action builder with a visual editor is several weeks that produce something the team may configure twice. I would ship the reminder engine, the gate rule editor and a fixed catalogue of ~15 configurable automations first, and build a general builder only if the catalogue proves too rigid in use.

**D · PHPStan level 6 is the right floor, not the right ceiling.** Level 6 across the app, and level 9 for `App\Domain\Finance` and `App\Domain\Gates` — the two namespaces where a type error is a money error or a safety error.

**E · Full offline on Charter Day is not what "poor signal" needs.** A durable retry queue with a visible pending-changes indicator solves the dock case at a fraction of the cost of true offline sync with conflict resolution. If genuine offline is required, it is a scoped addition with its own estimate. (Q26)

---

## Risks

| Risk | Handling |
|---|---|
| VAT treatment for international charters is wrong | Treatment is per-line data, not code; the client's tax advisor signs off before Phase 3 ships (Q5) |
| Commission basis assumed wrong → every P&L figure wrong | Blocking question, asked now, driven by `commission_rules` rather than hardcoded (Q4) |
| WhatsApp template approval takes 1–2 weeks | Start the WABA and template submission during Phase 2, long before Phase 7 needs it (Q14) |
| Data residency decided late | Blocking question; it determines the S3 provider, DB host and backup region (Q20) |
| Real flowcharts contain rules the brief's summary omits | Requested in Phase 0; every gate rule is data, so late rules are seed changes, not rewrites |
| Scope drift across 7 phases | Each phase ends with a walkthrough, a written statement of what was built/tested/deferred, and no phase starts before the prior one is accepted |

---

## Phase 1 — definition of done

Laravel 11 + Inertia + React 18 (TS strict), self-hosted DM Sans, the token layer above, the app shell (sidebar → rail → drawer, topbar, toolbar, page header) in both chrome themes and both directions, Fortify auth with mandatory TOTP and recovery codes, session list and revoke, the four roles with the seeded permission set, `owen-it` auditing live on every model created so far, the component library in §2–3 of `07-components.md` rendered at `/dev/ui`, CI running Pest + Vitest + PHPStan + ESLint + `tsc --noEmit`, and a login-to-empty-dashboard walkthrough on a deployed staging URL.

Tests that ship with it: the role/permission matrix as a data-driven feature test, the ownership-scope 404 tests, a 2FA enforcement test, and axe accessibility assertions on the shell.

---

## To proceed

1. Approve this plan, or mark what should change.
2. Answer the four blocking questions — **Q1** (UI chrome), **Q4** (commission basis), **Q5** (VAT treatment), **Q20** (hosting and data residency).
3. Send the source material listed at the end of `08-open-questions.md` — above all the original flowcharts and the real Cost & Offer spreadsheet.

On approval I start Phase 1 and report back with what was built, what was tested, what was deferred, and what needs deciding next.
