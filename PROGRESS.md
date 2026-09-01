# Walidia Yachts — Build Progress

Single source of truth for phase status. Updated at the end of every phase.

| Phase | Scope | Status | Notes |
|---|---|---|---|
| 0 | Plan: tokens, ERD, routes, permissions, gate rules, decisions | ✅ Delivered | `docs/phase-0/`, reviewed as an artifact |
| 1 | Foundation: Laravel + Inertia + React, tokens, app shell, auth + 2FA, RBAC, audit, component library | ✅ Delivered | 697 permissions, 4 roles, 13 auth tests |
| 2 | CRM core: leads, clients, companies, deals, fleet, tasks, documents, search | ✅ Delivered | 30 tables, ownership scoping, VIP field protection, 38 tests |
| 3 | Charter sales: enquiries, matching, proposals, bookings, cost sheets, invoices, payments, **the gate engine** | ✅ Delivered | Gate engine with 21 tests; enquiry → released charter passing end to end |
| 4 | Charter operations: checklists, crew, vendors, Charter Day, incidents, damage, deposits | ✅ Delivered | Mobile-first Charter Day; dispatch, boarding and deposit-release gates, 9 tests |
| 5 | Brokerage: listings, buyer requirements, NDAs, viewings, offers, surveys, transactions, handover | ✅ Delivered | NDA, proof-of-funds and ownership-transfer gates now active, 9 tests |
| 6 | Management and portals: managed yachts, maintenance, certificates, owner statements, portals | Not started | |
| 7 | Automation and reporting: workflow builder, reminders, WhatsApp, reports, website sync | Not started | Gate rule editor already shipped in Phase 3 |

## What is running today

- **Auth**: Fortify, mandatory TOTP, 12-character password policy with breach check, 5/min throttle, session list and revoke.
- **Authorisation**: 697 permissions across Sales, Operations, Finance and Admin; policies on every model; `ScopedToOwner` global scope so another agent's record is a 404 on every path.
- **CRM**: leads with an SLA clock, clients with encrypted identity fields and blind-index duplicate checking, companies, a three-pipeline deal board, tasks, the document vault.
- **Fleet**: one yacht record with charter/sale/management profiles; `yacht_availability_blocks` as the single writer of occupancy.
- **Charter**: enquiry → explainable matching → versioned proposal → acceptance (which locks the yacht and lays down the payment schedule) → booking.
- **Finance**: FTA-shaped tax invoices with gapless numbering allocated at issue, void-and-credit rather than edit, payments that only count once cleared, per-line VAT treatment.
- **The gate engine**: 17 rules as data, 9 checks, every evaluation recorded, overrides written to a read-only register, and a dry-run endpoint so a disabled button can say exactly what is missing.

## Verification

| Check | Command | State |
|---|---|---|
| Tests | `vendor/bin/pest` | 125 passing |
| Static analysis | `vendor/bin/phpstan analyse` | Level 6 |
| Formatting | `vendor/bin/pint` | Clean |
| Types | `npx tsc --noEmit` | Clean, strict mode |
| Build | `npm run build` | Clean |

## Phase 0 deliverables

| Deliverable | File |
|---|---|
| Summary of decisions and what happens next | `docs/phase-0/00-summary.md` |
| Design system, reconciled with the shared UI reference | `docs/phase-0/01-design-system.md` |
| Design token file (CSS custom properties) | `docs/phase-0/02-tokens.css` |
| Design token file (Tailwind theme) | `docs/phase-0/02-tokens.tailwind.ts` |
| Full ERD and schema | `docs/phase-0/03-erd.md` |
| Route map and CRUD coverage matrix | `docs/phase-0/04-routes.md` |
| Permission matrix and policy plan | `docs/phase-0/05-permissions.md` |
| Gate engine architecture | `docs/phase-0/06-gate-engine.md` |
| Gate rules as seedable data | `docs/phase-0/06-gate-rules.json` |
| Component inventory | `docs/phase-0/07-components.md` |
| Open questions, each with a proposed answer | `docs/phase-0/08-open-questions.md` |
| Architectural decision log | `DECISIONS.md` |

## Still blocking

The four questions from Phase 0 remain open, and the code carries the proposed
answers as configuration rather than assumptions:

- **Q1** interface chrome — both themes ship; `data-chrome` switches them.
- **Q4** commission basis — `config('walidia.commission')`, defaulted to profit for team and offer for agent.
- **Q5** VAT treatment — `vat_rates` rows plus per-line treatment; deposits and tips default to out of scope.
- **Q20** hosting and data residency — decides the S3 provider before anything is provisioned.

## Local setup

```
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
```

Seeded accounts (local only), password `Walidia!Harbour2026`:
`sales@walidia.test` · `operations@walidia.test` · `finance@walidia.test` · `admin@walidia.test`
Each must enrol a TOTP device on first sign-in — the mandate has no exceptions.
