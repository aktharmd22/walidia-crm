# 08 · Open Questions — one batch

Every question carries a **proposed answer**. Silence on any item means I build the proposal. Only the four marked **BLOCKING** stop work; the rest are answerable while earlier phases proceed. The phase column says when the answer is needed.

---

## Design and product

**Q1 · Sidebar chrome and accent colour — BLOCKING (Phase 1)**
The brief specifies a dark navy sidebar with a brass accent. The UI reference shared afterwards is a light sidebar with a blue accent and soft card shadows. The layout, components and density of that reference are adopted either way; only the chrome differs.
*Proposal:* build the shell with `data-chrome="navy"` + brass as the default (it is the brand direction, and it makes Walidia's tool look like nobody else's), ship `data-chrome="light"` + blue as a one-attribute alternative, and let the client see both on real data in the Phase 1 walkthrough before fixing it. Card shadows stay off in both; borders carry the hierarchy.

**Q2 · What can a Sales user actually see? (Phase 1)**
"Own clients and deals" is clear; the edges are not. Does a team lead see the team's book? What happens on reassignment — does the previous owner keep read access? Can two agents collaborate on one client?
*Proposal:* owner + explicit collaborators + team lead sees the team (`records.view-team`). On reassignment the previous owner loses access but keeps their name on the historical activity. Admin can grant a temporary named collaborator.

**Q3 · Who holds `clients.view-vip` by default? (Phase 2)**
The brief hides VIP personal data, guest manifests and dietary/allergy data behind a permission, but a charter cannot be catered by someone who cannot read an allergy list.
*Proposal:* the permission is granted per user, not per role. Default holders: Admin, the assigned Sales owner of that client, and the Operations manager assigned to that specific booking — the last one scoped to the booking window (72h before → 24h after). Everything logged.

**Q4 · Commission basis — BLOCKING (Phase 3)**
The cost sheet lists "team commission %" and "agent commission" as costs. Percentage **of what** — total offer, or total profit? Is agent commission a flat amount or a rate? Is commission accrued at booking, at deposit, or at completion?
*Proposal:* team commission = % of gross profit, agent/referral commission = % of total offer, both configurable per rule in `commission_rules`; accrued at confirmation, approved at completion, payable after closure. Please confirm — this changes every P&L figure in the system.

**Q5 · VAT treatment per line — BLOCKING (Phase 3)**
5% applies per service line, but the brief also mentions zero-rated and out-of-scope handling for international charters. Which lines are zero-rated or out of scope in practice: charters departing UAE waters, charters wholly outside the UAE (Seychelles, Maldives), security deposits (not a supply), crew tips (out of scope), disbursements recharged at cost?
*Proposal:* seed `vat_rates` with standard 5%, zero-rated, out-of-scope and reverse-charge; default deposits and tips to out-of-scope, treat foreign-marina charters as out-of-scope, and put the treatment on every line so it is visible and overridable per invoice. This needs the client's tax advisor to sign off before Phase 3 ships — I will build to the proposal and make it configurable rather than hardcoded.

**Q6 · Multi-currency (Phase 3/5)** — who sets the rate: manual entry at transaction date, or a daily feed?
*Proposal:* manual entry with a captured `exchange_rates` row (rate, date, source, who), because brokerage deals are negotiated at an agreed rate, not a market one. A daily feed can be added later as a default suggestion.

**Q7 · Deal ownership across business lines (Phase 2)** — if a charter guest later becomes a buyer, does the charter agent keep the client or does it pass to a broker?
*Proposal:* the client record stays with its owner; the new **deal** is assigned to the broker, and both see the client. Referral commission handles the economics.

**Q8 · Numbering formats (Phase 2)**
*Proposal:* `CL-2026-0001` clients, `LD-`, `EN-`, `PR-` proposals, `BK-2026-0001` bookings, `INV-2026-00001` (gapless per financial year), `CN-` credit notes, `RC-` receipts, `PO-`, `LS-` listings, `OF-` offers, `TR-` transactions, `OS-` owner statements. Configurable in Settings. Confirm the financial-year boundary: January, or something else?

**Q9 · Duplicate detection rule (Phase 2)** — what makes two client records the same person?
*Proposal:* exact match on normalised mobile (E.164) or on the passport/EID blind index = certain duplicate, blocked at creation with a merge prompt; fuzzy match on name + email domain = flagged for the Duplicates screen, never auto-merged.

**Q10 · Charter payment terms (Phase 3)** — standard deposit percentage, balance due date, APA percentage, and the cancellation tiers.
*Proposal placeholder:* 50% deposit on contract, balance 7 days before departure, APA 25–30% where applicable, cancellation 100% refund >30 days / 50% 30–7 days / 0% <7 days. These are placeholders in `cancellation_policies` — real numbers needed before Phase 3.

**Q11 · Security deposit mechanics (Phase 4)** — held as a card pre-authorisation, a cash deposit, or a transfer? How long after the charter before automatic release?
*Proposal:* support all three methods; automatic release 72 hours after the damage inspection closes, with a task to Finance rather than a silent refund.

**Q12 · Operational Release (Phase 3)** — Finance-only, or can Admin grant it? Can it be partial (deposit received but not cleared)?
*Proposal:* Finance and Admin. No partial state — an uncleared deposit is a blocked gate with a visible override path, which keeps the audit trail honest.

**Q13 · Crew (Phase 4)** — are crew employees, freelancers, or both? Is payroll in scope, or only per-charter payouts and tips?
*Proposal:* both employment types; payroll is **out of scope**, per-charter payouts and tip distribution are in scope.

---

## Integrations

**Q14 · WhatsApp Business API provider (Phase 7)** — Meta Cloud API direct, or a BSP (360dialog, Twilio, Infobip)? Is there an existing WABA and a verified business number?
*Proposal:* Meta Cloud API direct behind our interface — lowest per-message cost and no BSP lock-in — with a fake driver until credentials exist. Template approval lead time is 1–2 weeks; start it early.

**Q15 · E-signature provider (Phase 3)** — DocuSign, Dropbox Sign, Adobe, or the built-in fallback? UAE legal admissibility matters for a YPA.
*Proposal:* build the adapter interface plus the built-in signing page (audit trail, IP, timestamp, signed hash) for internal documents, and add one commercial provider for YPAs and management agreements once the client's counsel names an acceptable one.

**Q16 · Payment gateway (Phase 3)** — Network International, Telr, Checkout.com, Stripe UAE, or bank transfer only? Given transaction sizes, card may be irrelevant except for deposits.
*Proposal:* bank transfer as the primary method with manual reconciliation, plus one gateway for deposits and small balances via payment links. Name the bank so the reconciliation import format can be built.

**Q17 · WordPress sync (Phase 7)** — is the site's yacht data in a custom post type with ACF? Is the sync one-way (CRM → site), and does availability publish publicly?
*Proposal:* one-way CRM → site over a signed REST endpoint plus a small WP plugin; push specs, public media and price/availability *status* (available / on hold / booked), never guest or client data. Read access to the current site and its theme is needed.

**Q18 · Weather provider (Phase 4)** — StormGlass, Windy, or the UAE NCM feed? Marine forecast quality varies a lot in the Gulf.
*Proposal:* StormGlass for marine parameters behind the interface, cached per marina per hour.

---

## Compliance, data, operations

**Q19 · Arabic scope (Phase 1)** — full UI translation at launch, or RTL-ready with Arabic added progressively? Do client-facing PDFs and WhatsApp templates need Arabic first? Arabic-Indic numerals or Latin?
*Proposal:* RTL and the translation layer from commit one; UI strings translated in Phase 7; **client-facing** documents and message templates bilingual from Phase 3. Latin numerals in both locales, because finance staff reconcile against bank statements.

**Q20 · Hosting and data residency — BLOCKING (Phase 1)**
Given heads of state and royalty in the client list, is UAE data residency a requirement or a preference? This decides the S3 provider, the database host and the backup location.
*Proposal:* UAE region throughout (AWS me-central-1 or a UAE provider), with encrypted daily backups held in-region and a documented 30-day retention. Confirm before infrastructure is provisioned.

**Q21 · Data migration (Phase 2)** — what exists today, in what form, and how much of it? Spreadsheets, an old CRM, WhatsApp exports? Historic bookings needed, or a clean start with only active clients?
*Proposal:* import active clients, yachts, owners and open deals; archive historical bookings as read-only imports if the format allows. Send sample files early — the import mapper is built against real headers, not guesses.

**Q22 · Owner revenue share (Phase 6)** — share of gross charter revenue or of net after costs? Which costs are deductible? Statement cadence?
*Proposal:* configurable per `owner_agreements` (`gross` or `net`), with a fixed deductible-cost category list agreed once and applied consistently; monthly statements by default.

**Q23 · Brokerage commission (Phase 5)** — standard rate, and the split on co-brokerage. Who invoices the buyer's broker?
*Proposal:* 10% default, editable per agreement; 50/50 co-brokerage split; Walidia invoices the seller and pays the co-broker, so the money path matches the contract path.

**Q24 · AML threshold (Phase 5)** — the configurable value, and whether screening is manual (documented in the system) or an integrated provider.
*Proposal:* AED 1,000,000 as the default trigger, manual screening documented against `aml_screenings` with an uploaded report, provider integration deferred.

**Q25 · Guest manifest format (Phase 4)** — which authority receives it, and in what format? Abu Dhabi Maritime, Dubai Maritime City Authority and individual marinas may each differ.
*Proposal:* a configurable template per marina (`marinas.manifest_format`) producing PDF and CSV. Send one real example of each format currently in use.

**Q26 · Offline on Charter Day (Phase 4)** — how bad is the signal in practice? True offline is a significant build (service worker, local queue, conflict resolution).
*Proposal:* not full offline. Optimistic UI with a durable local write queue that retries and shows a clear "3 changes pending" indicator, plus a printable pre-departure summary. If real offline is required, it is a scoped addition to Phase 4 with its own estimate.

**Q27 · Approval Queue (Phase 2)** — the nav lists Clients → Approval Queue. What is being approved, and by whom: new client records, KYC, credit terms, or discounts?
*Proposal:* new clients created by Sales awaiting KYC/AML clearance before they can transact, approved by Admin or Compliance. Confirm whether discount approval belongs here too.

**Q28 · Reporting period and targets (Phase 7)** — does the business run on calendar months, a fiscal year, or a season? Are there per-agent targets the dashboard should show against?
*Proposal:* calendar months with a configurable fiscal year start; per-agent and per-yacht targets added as a Settings table if targets exist.

---

## What I need from the client to start Phase 1

1. Answers to **Q1, Q4, Q5, Q20** (the blocking four).
2. The original flowcharts and the Cost & Offer spreadsheet in their native format — I have been working from the summary in the brief, and the real files will settle several of the questions above without discussion.
3. Sample data: current yacht list with specs and photos, a recent charter cost sheet, a listing agreement, a YPA, an owner statement, and an FTA tax invoice they currently issue.
4. Access to the WordPress site (Q17), the logo and brand assets, and the licensed or intended use of DM Sans.
5. A named decision-maker for business rules, and a named finance contact for Q4/Q5/Q10.
