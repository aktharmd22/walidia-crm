# 06 · The Gate Engine — "what unlocks what"

The client's own documents open with that phrase, so it gets a real engine rather than scattered `if` statements (D-004). Everything below is data in `gate_rules`, editable by Admin, seeded from `06-gate-rules.json`.

---

## 1 · Shape

```
Actor clicks "Confirm booking"
        │
        ▼
BookingController::confirm ──► authorize()               ← policy: may this user, on this record
        │
        ▼
GateEvaluator::evaluate($subject, trigger: 'transition', to: 'confirmed', user)
        │
        ├─ load active gate_rules matching (subject_type, trigger)      [cached, tagged, busted on rule save]
        ├─ for each rule, for each condition → CheckRegistry::resolve($key)->passes($subject, $params)
        ├─ persist a gate_evaluations row (always, pass or fail)
        └─ return GateResult { verdict: pass|warn|block, failures: [ {rule, condition, message, resolution} ] }
        │
        ├─ block  → 422 with the failure list, unless an override is attached
        ├─ warn   → proceed, raise a dashboard flag + a task
        └─ pass   → dispatch the transition action
```

`GateResult` is the single object the UI, the API and the tests all read. There is no second code path where a transition happens without passing through the evaluator — enforced by putting every transition behind an Action class whose constructor takes the evaluator.

## 2 · Checks

A check is a small, single-purpose, individually tested class:

```php
interface GateCheck
{
    public function key(): string;                                   // 'payment.deposit_cleared'
    public function passes(Model $subject, array $params): bool;
    public function failureMessage(Model $subject, array $params): string;  // names the missing thing
    public function resolution(Model $subject, array $params): ?Resolution; // route + label to fix it
}
```

Registry (Phase 3–5 delivery order):

| Key | Passes when | Params |
|---|---|---|
| `proposal.accepted` | The linked proposal is accepted and not expired | — |
| `kyc.verified` | Client KYC status is `verified` and not expired | `expiry_months` |
| `payment.deposit_cleared` | Allocated cleared payments ≥ the deposit schedule item | `schedule_label` |
| `booking.operational_release` | `operational_release_at` is set | — |
| `guests.identity_verified` | Every guest on the manifest has a verified document | `allow_lead_only` |
| `checklist.item_complete` | The named blocking checklist item is done | `item_key` |
| `damage.inspection_closed` | No open damage report on the booking | — |
| `nda.signed` | A signed, unexpired NDA exists for this client + listing | — |
| `buyer.identity_verified` | Buyer client KYC/ID verified | — |
| `offer.proof_of_funds` | Proof-of-funds document present when the listing requires it | — |
| `transaction.funds_cleared` | Final payment cleared **and** reconciled | — |
| `yacht.certificates_valid` | Registration, insurance and safety certificates are all valid on the charter date | `types[]`, `date_field` |
| `crew.documents_valid` | No assigned crew document expired (hard) or expiring inside N days (soft) | `within_days` |
| `payouts.issued` | Every accrued payout on the booking/deal is paid | — |
| `receipts.generated` | Every cleared payment has a receipt | — |
| `itinerary.present` | The booking has an itinerary | — |
| `weather.checked` | A marine forecast was pulled within N hours of departure | `within_hours` |
| `manifest.complete` | Guest count on the manifest equals the booked guest count | — |
| `listing.agreement_active` | Listing agreement is signed, active, and not expiring inside N days | `expiring_days` |
| `option_hold.valid` | The availability hold has not passed its expiry | `warn_hours` |
| `aml.cleared` | AML screening clear when the amount exceeds the configured threshold | `threshold`, `currency` |

Adding a new *kind* of check is a code change; adding, retargeting, reordering or disabling a *rule* is a data change.

## 3 · Severity

**Hard** — blocks. The action returns 422 with the failure list. Only `gates.override` can proceed, and only with a reason; the override writes `gate_overrides` and appears in the Override Register forever.

**Soft** — allows, but records a warning on the evaluation, raises a dashboard flag on Alerts & Blockers, and (when the rule says so) opens a task assigned by role. Soft gates are the early-warning system: expiring certificates, missing itinerary, no weather check, incomplete manifest, expiring listing agreement, an option hold about to lapse.

## 4 · The UI contract — a blocked button always explains itself

Every screen that can trigger a guarded transition calls `POST /gates/evaluate` (dry run) when it loads, and renders:

- **pass** — the button is enabled.
- **warn** — the button is enabled, with an amber inline note above it listing the warnings.
- **block** — the button is disabled, in `--neutral`, with a tooltip and an inline panel that names *exactly* what is missing, one line per failed condition, each with a link to the screen that resolves it:

```
┌────────────────────────────────────────────────────────┐
│ ⚠  Cannot grant Operational Release                    │
│                                                        │
│ ●  Deposit not received                                │
│    AED 150,000 of AED 200,000 cleared.                 │
│    → Open payment schedule                             │
│                                                        │
│ ●  Insurance certificate expires before the charter    │
│    Expires 12 Mar 2026, charter 20 Mar 2026.           │
│    → Open yacht certificates                           │
│                                                        │
│ [ Request override ]        (Admin only)               │
└────────────────────────────────────────────────────────┘
```

Never a silent failure, never a generic "not allowed". Dragging a pipeline card that fails a hard gate animates back to its column and opens the same panel.

## 5 · Override flow

1. Admin clicks **Request override** on the failure panel.
2. Modal: the failed conditions (read-only), a mandatory free-text reason (min 20 characters), and an explicit acknowledgement checkbox naming the risk.
3. On submit: `gate_overrides` row (rule, evaluation, subject, user, reason, IP, user agent), the transition proceeds in the same transaction, an `activities` entry lands on the record timeline, and a notification goes to the other Admins.
4. The Override Register (`/compliance/overrides`) lists every one. Read-only for everybody, including Admin. No route exists that edits or deletes it.

## 6 · Configuration UI

`/automation/gate-rules` — a list of rules grouped by subject, each showing trigger, severity, conditions and an active toggle. Editing opens a drawer with a condition builder over the registry. `Test rule` runs a dry evaluation against a real record and shows the result without writing anything. Rule edits are audited and versioned (`gate_rules.version`), so "who loosened the boarding gate, and when" is answerable.

## 7 · Testing (these are the tests that matter most — written first)

For each of the 17 seeded rules: a passing case, a blocking case asserting the exact message and resolution route, an override case asserting both the transition and the register row, and a permission case asserting a non-Admin cannot override. Plus engine-level tests: an inactive rule is skipped, a soft failure does not block, rules evaluate in `sort_order`, evaluation is written on both outcomes, and no transition Action can be constructed without an evaluator (asserted architecturally).
