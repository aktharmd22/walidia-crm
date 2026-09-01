# 04 · Route Map and CRUD Coverage

Inertia pages plus the actions behind them. Everything is session-authenticated, `verified`, `2fa`-confirmed, policy-authorised and rate-limited on writes. The only unauthenticated surfaces are the signed-link routes in §6.

---

## 1 · Conventions

**Every business entity gets the full seven, plus restore** (D-018):

| Verb | URI | Route name | Screen |
|---|---|---|---|
| GET | `/{module}` | `{module}.index` | List — table or board, with filters, saved views, bulk bar, pagination |
| GET | `/{module}/create` | `{module}.create` | Create — drawer on desktop, full page on mobile |
| POST | `/{module}` | `{module}.store` | — |
| GET | `/{module}/{id}` | `{module}.show` | Detail — split pane: record + timeline |
| GET | `/{module}/{id}/edit` | `{module}.edit` | Edit — drawer, prefilled |
| PUT/PATCH | `/{module}/{id}` | `{module}.update` | — |
| DELETE | `/{module}/{id}` | `{module}.destroy` | Soft delete, confirmation required |
| POST | `/{module}/{id}/restore` | `{module}.restore` | From the Archive view |

**And on every index:**

| Verb | URI | Name | Purpose |
|---|---|---|---|
| POST | `/{module}/bulk` | `{module}.bulk` | assign · status · tag · archive — same policies, same gates, queued above 500 rows |
| GET | `/{module}/export` | `{module}.export` | CSV/XLSX of the current filter set; writes an `exports` row |
| GET | `/{module}/import` | `{module}.import` | Mapping + preview screen |
| POST | `/{module}/import/preview` | `{module}.import.preview` | Validates row by row, flags duplicates |
| POST | `/{module}/import/commit` | `{module}.import.commit` | Queued, produces `import_rows` outcomes |
| GET | `/{module}/archive` | `{module}.archive` | Soft-deleted records, restorable |
| POST | `/{module}/views` · PUT · DELETE | `{module}.views.*` | Saved filter views |

Sub-resources are nested and get their own full CRUD: `/charter/proposals/{proposal}/items/{item}`, `/finance/invoices/{invoice}/items/{item}`, `/charter/checklists/{checklist}/items/{item}`, `/brokerage/offers/{offer}/counters`, `/fleet/yachts/{yacht}/media/{media}`, and so on.

**Non-CRUD state changes are their own named POST routes**, never a PATCH of a status field from the client — because each one is a gate-evaluated transition: `bookings.confirm`, `bookings.release-operations`, `offers.accept`, `listings.activate`. The React layer never sends a target status it invented.

**Read-only by design** (no create/update/delete routes): `audits`, `gate_evaluations`, `gate_overrides`, `record_access_logs`, `sequences`, `webhook_events`, `workflow_runs`. The Override Register is a screen over the second and third.

---

## 2 · CRUD coverage matrix

C = create, R = read/list, U = update, D = archive (soft delete), Rs = restore, B = bulk actions, IE = import/export.
Owner column = the role that holds create/update by default; Admin holds everything.

| Entity | C | R | U | D | Rs | B | IE | Owner |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|---|
| users | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Admin |
| roles · permissions | ✔ | ✔ | ✔ | ✔ | — | — | — | Admin |
| teams | ✔ | ✔ | ✔ | ✔ | ✔ | — | — | Admin |
| leads | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| lead_sources | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Admin |
| clients | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| client_contacts | ✔ | ✔ | ✔ | ✔ | ✔ | — | — | Sales |
| companies | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| deals | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| pipelines · stages · lost_reasons | ✔ | ✔ | ✔ | ✔ | ✔ | — | — | Admin |
| tasks | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | All |
| notes · activities | ✔ | ✔ | ✔¹ | ✔ | — | — | ✔ | All |
| tags | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | — | All |
| yachts | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| yacht_*_profiles | ✔ | ✔ | ✔ | ✔ | — | — | ✔ | Ops / Sales |
| yacht_media | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | — | Ops |
| yacht_inventory_items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| yacht_availability_blocks | ✔ | ✔ | ✔ | ✔ | — | — | — | Ops |
| yacht_owners · owner_agreements | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Admin |
| marinas · berths | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Ops |
| charter_enquiries | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| charter_matches | ✔² | ✔ | ✔ | ✔ | — | ✔ | — | Sales |
| charter_proposals · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| bookings | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| booking_guests · guest_manifests | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔³ | Ops |
| cost_sheets · lines | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Sales / Finance |
| checklist_templates · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| operations_checklists · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| charter_day_logs | ✔ | ✔ | —⁴ | —⁴ | — | — | ✔ | Ops |
| charter_extras | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | — | Ops |
| incidents · damage_reports | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Ops |
| security_deposits | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Finance |
| charter_feedback | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| cancellation_policies | ✔ | ✔ | ✔ | ✔ | ✔ | — | — | Admin |
| listing_agreements | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| listings | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| valuations | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Sales |
| buyer_requirements | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| ndas | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | — | Sales |
| viewings · feedback | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| offers · counters | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Sales |
| surveys · sea_trials | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Sales |
| transactions · milestones | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Sales / Finance |
| handovers · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| aml_screenings | ✔ | ✔ | ✔ | ✔ | — | — | ✔ | Admin |
| crew | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| crew_documents · assignments · payouts | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops / Finance |
| vendors · categories · documents · ratings | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| purchase_orders · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| management_agreements | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Admin |
| maintenance_schedules · logs | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| certificates | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Ops |
| owner_statements · lines | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| quotations · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Sales |
| invoices · items | ✔ | ✔ | ✔⁵ | ✔⁵ | — | ✔ | ✔ | Finance |
| payment_schedules · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| payments · allocations | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| receipts · refunds | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Finance |
| commission_rules · commissions | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| payouts · items | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| expenses | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Finance |
| vat_records · vat_rates · bank_charges · exchange_rates | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Finance |
| documents · versions · templates | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | All (scoped) |
| signature_requests | ✔ | ✔ | ✔ | ✔ | — | ✔ | — | Sales |
| gate_rules | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Admin |
| workflows · steps | ✔ | ✔ | ✔ | ✔ | ✔ | — | ✔ | Admin |
| message_templates · reminder_rules | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Admin |
| communications | ✔⁶ | ✔ | — | ✔ | — | — | ✔ | All |
| notification_preferences · saved_views · user_preferences | ✔ | ✔ | ✔ | ✔ | — | — | — | Self |
| settings · sequences · integrations | ✔ | ✔ | ✔ | ✔ | — | — | ✔ | Admin |
| list_options | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | Admin |
| portal_invitations | ✔ | ✔ | ✔ | ✔ | — | ✔ | — | Admin |
| audits · gate_evaluations · gate_overrides · record_access_logs · webhook_events · workflow_runs | — | ✔ | — | — | — | — | ✔ | Admin (read-only) |

¹ Notes and activities are editable by their author for 24 hours, then locked; the edit is audited either way.
² Matches are generated by the matching engine but can be added and removed by hand.
³ Manifest export only, permission-gated, and each export writes a `record_access_logs` row.
⁴ Charter day logs are append-only. A correction adds a `correction` entry referencing the original.
⁵ An issued invoice is never edited or deleted — it is voided and credited (a credit note). Draft invoices are fully editable.
⁶ Create = send. Outbound messages are created, never updated.

---

## 3 · Routes by module

Each `resource(...)` below expands to the eight routes in §1 plus the index extras. Only the additional, module-specific actions are listed.

### Dashboard
```
GET  /                              dashboard.my-day
GET  /dashboard/pipeline            dashboard.pipeline
GET  /dashboard/alerts              dashboard.alerts          # gate blockers, expiries, overdue
GET  /dashboard/calendar            dashboard.calendar
```

### Leads
```
resource('leads')
GET  /leads/inbox                   leads.inbox
GET  /leads/unassigned              leads.unassigned
GET  /leads/follow-up               leads.follow-up
GET  /leads/duplicates              leads.duplicates
POST /leads/{lead}/assign           leads.assign
POST /leads/{lead}/qualify          leads.qualify
POST /leads/{lead}/convert          leads.convert            # → client + enquiry/requirement + deal
POST /leads/{lead}/merge            leads.merge
POST /leads/{lead}/log-contact      leads.log-contact
```

### Clients
```
resource('clients')                 # + individuals/companies/vip/buyers/sellers/owners/partners scopes
resource('clients.contacts')
resource('companies')
GET  /clients/vip                   clients.vip              # requires clients.view-vip
GET  /clients/approval-queue        clients.approval-queue
POST /clients/{client}/approve      clients.approve
POST /clients/{client}/merge        clients.merge
POST /clients/{client}/kyc          clients.kyc.submit
POST /clients/{client}/kyc/verify   clients.kyc.verify       # Compliance/Admin
GET  /clients/{client}/timeline     clients.timeline
GET  /clients/{client}/documents    clients.documents
POST /clients/{client}/portal-invite clients.portal.invite
```

### Charter
```
resource('charter/enquiries')
GET  /charter/enquiries/{enquiry}/matching        charter.matching
POST /charter/enquiries/{enquiry}/matches         charter.matches.generate
resource('charter/proposals')  + resource('charter/proposals.items')
POST /charter/proposals/{proposal}/send           charter.proposals.send
POST /charter/proposals/{proposal}/duplicate      charter.proposals.duplicate
POST /charter/proposals/{proposal}/version        charter.proposals.version
GET  /charter/proposals/{proposal}/pdf            charter.proposals.pdf
POST /charter/proposals/{proposal}/accept         charter.proposals.accept      # GATE: locks availability
resource('charter/bookings')  + resource('charter/bookings.guests')
POST /charter/bookings/{booking}/hold             bookings.hold
POST /charter/bookings/{booking}/contract         bookings.contract.generate    # GATE: KYC verified
POST /charter/bookings/{booking}/confirm          bookings.confirm
POST /charter/bookings/{booking}/release-operations bookings.release-operations # GATE: deposit cleared
POST /charter/bookings/{booking}/board            bookings.board                # GATE: ID + safety brief
POST /charter/bookings/{booking}/complete         bookings.complete
POST /charter/bookings/{booking}/cancel           bookings.cancel
GET  /charter/bookings/{booking}/manifest         bookings.manifest
POST /charter/bookings/{booking}/manifest/export  bookings.manifest.export
GET  /charter/calendar                            charter.calendar
resource('charter/cost-sheets') + resource('charter/cost-sheets.lines')
POST /charter/cost-sheets/{sheet}/copy-to/{phase} cost-sheets.copy-phase
POST /charter/cost-sheets/{sheet}/close           cost-sheets.close             # GATE: payouts + receipts
resource('charter/checklists') + resource('charter/checklists.items')
POST /charter/checklists/{checklist}/apply-template checklists.apply-template
GET  /charter/day/{booking}                       charter.day                   # mobile-first
POST /charter/day/{booking}/log                   charter.day.log
POST /charter/day/{booking}/extras                charter.day.extras.store
resource('charter/incidents')
resource('charter/damage-reports')
POST /charter/damage-reports/{report}/close       damage.close                  # GATE unlocks deposit
resource('charter/security-deposits')
POST /charter/security-deposits/{d}/release       deposits.release              # GATE: inspection closed
resource('charter/feedback')
GET  /charter/pnl                                 charter.pnl
GET  /charter/pnl/{booking}                       charter.pnl.show
```

### Brokerage
```
resource('brokerage/buyer-requirements')
resource('brokerage/listing-agreements')
resource('brokerage/listings')
POST /brokerage/listings/{listing}/activate        listings.activate
POST /brokerage/listings/{listing}/withdraw        listings.withdraw
POST /brokerage/listings/{listing}/price           listings.price.update       # writes price_history
GET  /brokerage/matching                           brokerage.matching
resource('brokerage/valuations')
resource('brokerage/ndas')
POST /brokerage/ndas/{nda}/send                    ndas.send
resource('brokerage/viewings') + resource('brokerage/viewings.feedback')
POST /brokerage/viewings/{viewing}/verify-buyer    viewings.verify              # GATE input
POST /brokerage/viewings/{viewing}/complete        viewings.complete
resource('brokerage/offers')
POST /brokerage/offers/{offer}/submit              offers.submit                # GATE: proof of funds
POST /brokerage/offers/{offer}/counter             offers.counter
POST /brokerage/offers/{offer}/accept              offers.accept
POST /brokerage/offers/{offer}/reject              offers.reject
resource('brokerage/surveys')  resource('brokerage/sea-trials')
resource('brokerage/transactions') + resource('brokerage/transactions.milestones')
POST /brokerage/transactions/{t}/transfer-ownership transactions.transfer       # GATE: funds cleared
resource('brokerage/handovers') + resource('brokerage/handovers.items')
GET  /brokerage/pnl                                brokerage.pnl
```

### Management · Fleet · Crew · Vendors
```
resource('management/agreements')  resource('management/maintenance-schedules')
resource('management/maintenance-logs')  resource('management/purchase-orders')
POST /management/purchase-orders/{po}/approve       po.approve
POST /management/purchase-orders/{po}/receive       po.receive
resource('management/owner-statements')
POST /management/owner-statements/{s}/generate      statements.generate
POST /management/owner-statements/{s}/issue         statements.issue
GET  /management/reports                            management.reports
GET  /management/safety                             management.safety

resource('fleet/yachts') + nested media, inventory, availability, documents, owners
GET  /fleet/availability                            fleet.availability
POST /fleet/yachts/{yacht}/sync-website             fleet.sync
resource('fleet/marinas')  resource('fleet/berths')

resource('crew') + nested documents, assignments, payouts
GET  /crew/expiry                                   crew.expiry
POST /crew/assignments/{a}/dispatch                 crew.dispatch                # GATE: op. release
POST /crew/assignments/{a}/acknowledge              crew.acknowledge

resource('vendors') + nested documents, ratings; resource('vendors/categories')
```

### Finance
```
resource('finance/quotations') + items
POST /finance/quotations/{q}/convert                quotations.convert
resource('finance/invoices') + items
POST /finance/invoices/{invoice}/issue              invoices.issue
POST /finance/invoices/{invoice}/void               invoices.void
POST /finance/invoices/{invoice}/credit-note        invoices.credit-note
POST /finance/invoices/{invoice}/send               invoices.send
POST /finance/invoices/{invoice}/payment-link       invoices.payment-link
GET  /finance/invoices/{invoice}/pdf                invoices.pdf
resource('finance/payment-schedules') + items
resource('finance/payments') + allocations
POST /finance/payments/{payment}/reconcile          payments.reconcile
POST /finance/payments/{payment}/confirm-deposit    payments.confirm-deposit     # unlocks op. release
resource('finance/receipts')  resource('finance/refunds')
resource('finance/commissions')  resource('finance/commission-rules')
POST /finance/commissions/{c}/approve               commissions.approve
resource('finance/payouts') + items
POST /finance/payouts/{p}/approve  POST /finance/payouts/{p}/pay
resource('finance/expenses')  resource('finance/vat-records')  resource('finance/vat-rates')
resource('finance/bank-charges')  resource('finance/exchange-rates')
GET  /finance/overdue        GET /finance/pnl        GET /finance/closure
```

### Documents · Compliance · Communications · Automation · Tasks · Reports · Settings
```
resource('documents') + resource('documents.versions') + resource('documents/templates')
GET  /documents/{document}/download                 documents.download           # policy + access log
GET  /documents/pending-signature                   documents.pending
GET  /documents/expiry                              documents.expiry
resource('documents/signature-requests')

GET  /compliance/kyc-queue                          compliance.kyc
GET  /compliance/id-verification                    compliance.id
resource('compliance/certificates')
GET  /compliance/audit-log                          compliance.audit             # read-only
GET  /compliance/overrides                          compliance.overrides         # read-only register
resource('compliance/aml-screenings')

GET  /communications/whatsapp  /email  /log         communications.*
POST /communications/send                           communications.send
resource('communications/templates')

resource('automation/workflows') + steps
POST /automation/workflows/{w}/toggle · /test       workflows.*
resource('automation/gate-rules')
POST /automation/gate-rules/{rule}/test             gate-rules.test              # dry-run vs a record
POST /automation/gate-rules/reorder                 gate-rules.reorder
resource('automation/reminder-rules')
resource('automation/notifications')                # preferences

resource('tasks')  GET /tasks/team · /next-actions · /overdue
POST /tasks/{task}/complete · /reopen · /escalate

GET  /reports/{sales|utilisation|fleet-revenue|pipeline|commissions|owner-statements}
resource('reports/custom')                          # saved report definitions, full CRUD

resource('settings/users')  resource('settings/roles')  resource('settings/pipelines')
resource('settings/lists')  resource('settings/rate-cards')  resource('settings/cancellation-policies')
GET|PUT /settings/company · /currencies-tax · /integrations · /website-sync · /sequences
GET  /settings/import-export                        settings.import-export
```

---

## 4 · Cross-cutting endpoints

```
GET  /search?q=                     search.global      # clients, yachts, bookings, listings, docs
GET  /search/suggest                search.suggest     # ⌘K palette
POST /gates/evaluate                gates.evaluate     # dry-run for the UI: why is this button disabled
POST /gates/{rule}/override         gates.override     # requires reason + permission, writes the register
GET  /notifications                 notifications.index
POST /notifications/{n}/read · /read-all
GET  /me/sessions  DELETE /me/sessions/{id}           # concurrent session revoke
GET|PUT /me/profile · /me/security · /me/preferences
```

---

## 5 · Portals (separate guard, separate layout, no CRM seat)

```
/portal/owner      dashboard · calendar · revenue · statements · maintenance · documents
/portal/partner    referrals · commissions · statements
/portal/crew       my assignments · checklist · acknowledge · documents (mobile-first)
```
Each portal user is a `users` row on the `portal` guard, bound to exactly one client or vendor, with a hard global scope. Portal routes read; the only writes are checklist acknowledgement, document upload and profile.

## 6 · Signed, session-free client links (7 days, single purpose)

```
GET|POST /l/{token}/itinerary       links.itinerary     # approve / request change
GET|POST /l/{token}/sign            links.sign          # fallback e-signature page
GET      /l/{token}/pay             links.pay           # gateway hand-off
GET|POST /l/{token}/feedback        links.feedback
GET      /l/{token}/document        links.document      # single document, watermarked
```
Tokens are stored hashed, single-purpose, use-capped, revocable, and rate-limited. They grant no session and expose no other record.

## 7 · Webhooks (public, signature-verified, idempotent)

```
POST /webhooks/whatsapp   /webhooks/payments   /webhooks/esign   /webhooks/website
```
Every call is written to `webhook_events` before processing, verified by provider signature, and replayable from the Admin UI.
