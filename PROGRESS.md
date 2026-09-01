# Walidia Yachts — Build Progress

Single source of truth for phase status. Updated at the end of every phase.

| Phase | Scope | Status | Notes |
|---|---|---|---|
| 0 | Plan: tokens, ERD, routes, permissions, gate rules, decisions | **Delivered — awaiting approval** | No application code written. See `docs/phase-0/`. |
| 1 | Foundation: Laravel + Inertia + React, tokens, app shell, auth + 2FA, RBAC, audit, component library | Not started | Blocked on Phase 0 approval + Q1 (UI theme), Q20 (hosting) |
| 2 | CRM core: leads, clients, companies, yacht registry, document vault, timeline, tasks, search | Not started | |
| 3 | Charter sales: enquiries, matching, proposals, bookings, cost sheets, invoices, payments, Operational Release | Not started | Blocked on Q4, Q5, Q10 |
| 4 | Charter ops: checklists, crew, vendors, Charter Day (mobile-first), incidents, deposits, P&L | Not started | Blocked on Q26 (offline) |
| 5 | Brokerage: listings, matching, viewings + NDA gate, offers, surveys, transactions, handover, deal P&L | Not started | Blocked on Q23, Q24 |
| 6 | Management + portals: managed yachts, maintenance, certificates, owner statements, owner/partner portals | Not started | Blocked on Q22 |
| 7 | Automation + reporting: workflow builder, gate rule editor, WhatsApp, reminders, reports, website sync | Not started | Blocked on Q14–Q17 |

## Phase 0 deliverables

| Deliverable | File |
|---|---|
| Summary of decisions and what happens next | `docs/phase-0/00-summary.md` |
| Design system, reconciled with the shared UI reference | `docs/phase-0/01-design-system.md` |
| Design token file (CSS custom properties) | `docs/phase-0/02-tokens.css` |
| Design token file (Tailwind theme) | `docs/phase-0/02-tokens.tailwind.ts` |
| Full ERD and schema | `docs/phase-0/03-erd.md` |
| Route map | `docs/phase-0/04-routes.md` |
| Permission matrix and policy plan | `docs/phase-0/05-permissions.md` |
| Gate engine architecture | `docs/phase-0/06-gate-engine.md` |
| Gate rules as seedable data | `docs/phase-0/06-gate-rules.json` |
| Component inventory (from the shared UI) | `docs/phase-0/07-components.md` |
| Open questions — one batch, each with a proposed answer | `docs/phase-0/08-open-questions.md` |
| Architectural decision log | `DECISIONS.md` |
