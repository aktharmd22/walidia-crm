# 05 · Permissions, Policies and Visibility

Three independent layers, all server-side. The React layer only decides what to *render*; it never decides what is *allowed*.

1. **Visibility** — which rows exist for this user at all (`ScopedToOwner` global scope, D-017).
2. **Permission** — may this user perform this verb on this entity type (`spatie/laravel-permission`).
3. **Policy** — may this user perform it on *this record*, in *this state* (Laravel Policies, plus the gate engine for transitions).

A request must pass all three. Every controller action calls `authorize()`; a `ChecksAuthorization` test asserts that no controller method exists without one.

---

## 1 · Roles

| Role | Intent | Default visibility |
|---|---|---|
| **Sales** | Leads → enquiries → proposals → bookings; listings, viewings, offers | Own records, plus team records if they are a team lead |
| **Operations** | Checklists, crew, vendors, charter day, fleet, maintenance | All confirmed bookings and all fleet — but no financial amounts and no VIP fields |
| **Finance** | Invoices, payments, VAT, commissions, payouts, P&L | All financial data across all records; read-only on the operational records those figures hang from |
| **Admin** | Everything, plus overrides, audit, compliance, settings | All |

Roles are additive; a user may hold several. Two **modifier permissions** further restrict Sales, per the brief: `line.charter` and `line.brokerage`. Holding neither means both (the default); holding one restricts every Sales screen and query to that business line.

---

## 2 · Permission naming

`{entity}.{action}` with actions `view · create · update · delete · restore · export · import`, plus entity-specific verbs for state changes: `bookings.confirm`, `bookings.release-operations`, `invoices.issue`, `invoices.void`, `offers.accept`, `deposits.release`, `crew.dispatch`, `listings.activate`, `transactions.transfer`.

Cross-cutting permissions:

| Permission | Meaning |
|---|---|
| `records.view-all` | Bypasses the ownership scope (Ops, Finance, Admin) |
| `records.view-team` | Sees the team's records (team leads) |
| `records.reassign` | Changes `assigned_user_id` |
| `clients.view-vip` | Sees VIP-protected fields: passport/EID, dietary, allergies, VIP notes, manifests |
| `clients.export-pii` | Exports any file containing client PII |
| `finance.view-amounts` | Sees money anywhere in the app (Ops does **not** hold this) |
| `gates.override` | Overrides a hard gate; always writes to the Override Register with a mandatory reason |
| `compliance.verify-kyc` | Marks KYC verified |
| `compliance.view-audit` | Reads the audit log and the Override Register |
| `settings.manage` | Settings, roles, sequences, integrations |
| `automation.manage` | Workflows, gate rules, reminder rules, message templates |

---

## 3 · Matrix

● = full · ◐ = read only · ○ = none · ▲ = own records only · ✎ = create/update but not delete

| Area | Sales | Ops | Finance | Admin |
|---|:--:|:--:|:--:|:--:|
| Dashboard (own scope) | ● | ● | ● | ● |
| Leads | ▲ | ○ | ○ | ● |
| Clients & companies | ▲ | ◐¹ | ◐¹ | ● |
| VIP fields (`clients.view-vip`) | ○² | ○² | ○ | ● |
| KYC submit / verify | ✎ / ○ | ○ | ○ | ● / ● |
| Deals & pipelines | ▲ | ○ | ◐ | ● |
| Charter enquiries · matching · proposals | ● | ◐ | ◐ | ● |
| Bookings | ● | ◐ + ops verbs | ◐ | ● |
| `bookings.release-operations` | ○ | ○ | ● | ● |
| Cost sheets | ✎ (quoted phase) | ○ | ● (all phases) | ● |
| Operations checklists · charter day · incidents | ◐ | ● | ○ | ● |
| Guest manifests | ◐² | ●² | ○ | ● |
| Damage reports · security deposits | ◐ | ✎ (damage) | ● (deposit) | ● |
| Listings · agreements · valuations | ● | ◐ | ◐ | ● |
| Viewings · NDAs · offers · surveys | ● | ○ | ◐ | ● |
| Transactions · handover | ✎ | ✎ (handover) | ● (funds) | ● |
| Fleet · yachts · media · availability | ◐ | ● | ○ | ● |
| Crew · assignments · documents | ○ | ● | ◐ (payouts ●) | ● |
| Vendors · POs | ○ | ● | ◐ (payment ●) | ● |
| Management · maintenance · certificates | ○ | ● | ○ | ● |
| Owner statements | ○ | ○ | ● | ● |
| Quotations · invoices · payments · refunds | ◐ | ○ | ● | ● |
| Commissions · payouts · VAT · P&L | ◐ (own commission) | ○ | ● | ● |
| Documents vault | ▲ scoped | ● fleet/ops | ● finance | ● |
| Compliance · audit log · Override Register | ○ | ○ | ◐ | ◐³ |
| `gates.override` | ○ | ○ | ○ | ● |
| Communications · templates | ● / ◐ | ● / ◐ | ◐ | ● |
| Automation · workflows · gate rules | ○ | ○ | ○ | ● |
| Tasks | ● own / ◐ team | ● own | ● own | ● |
| Reports | ▲ own performance | ● operational | ● financial | ● |
| Settings · users · roles | ○ | ○ | ○ | ● |

¹ Ops and Finance see the client records attached to work they own — Ops via confirmed bookings, Finance via invoices — without the ownership scope granting them the whole book.
² VIP fields and manifests are additionally gated on `clients.view-vip`, which is granted per user rather than per role, and every access is logged (Q3).
³ The audit log and Override Register are read-only for everyone, including Admin. There is no route that writes or edits them.

---

## 4 · Policies

One policy per model, all of them registered explicitly (no auto-discovery, so a missing policy fails loudly). Each implements `viewAny · view · create · update · delete · restore · forceDelete` plus the entity's state verbs.

Representative rules that are not merely permission checks:

| Policy | Rule |
|---|---|
| `ClientPolicy::view` | Permission **and** (owner ∨ `records.view-all` ∨ team lead ∨ has an active booking/invoice the viewer owns) |
| `ClientPolicy::viewVipFields` | `clients.view-vip`; writes `record_access_logs` on every pass |
| `BookingPolicy::update` | Blocked once `status = completed` unless Admin; blocked entirely once the cost sheet is closed |
| `BookingPolicy::releaseOperations` | Finance or Admin only, and delegates to the gate engine for the deposit condition |
| `InvoicePolicy::update` | Draft only. Issued invoices are void-and-credit, never edited |
| `InvoicePolicy::delete` | Always false. Issued tax invoices are not deletable at any permission level |
| `CostSheetPolicy::updateLine` | Sales may write `quoted` lines; Finance may write `invoiced` and `actual`; nobody may write a closed sheet |
| `DocumentPolicy::download` | Policy on the *subject*, not the document: if you cannot see the booking, you cannot see its contract |
| `GateOverridePolicy` | `create` requires `gates.override` + a non-empty reason; `update`/`delete` always false |
| `CrewAssignmentPolicy::dispatch` | Ops or Admin, and the gate engine must pass Operational Release |
| `AuditPolicy` | `viewAny` requires `compliance.view-audit`; every other ability false |

---

## 5 · Visibility scopes

```php
// Applied to: Client, Lead, Deal, CharterEnquiry, CharterProposal, Booking,
// Listing, BuyerRequirement, Viewing, Offer, Task, Document, Activity.
class ScopedToOwner implements Scope
{
    public function apply(Builder $q, Model $m): void
    {
        $user = auth()->user();
        if (! $user || $user->can('records.view-all')) return;

        $q->where(function (Builder $q) use ($user, $m) {
            $q->where($m->getTable().'.assigned_user_id', $user->id);
            if ($user->can('records.view-team')) {
                $q->orWhereIn($m->getTable().'.assigned_user_id', $user->teamMemberIds());
            }
            $q->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id));
        });
    }
}
```

- Applies to every query path — index, relation, global search, report, and a direct `find()` — so guessing an ID returns 404, not 403 (a 403 confirms the record exists).
- `withoutOwnerScope()` is explicit, grep-auditable and permitted only inside reporting queries and console commands.
- The business-line modifier is a second scope on the same models, keyed off `line.charter` / `line.brokerage`.
- Portal users get a hard, unbypassable scope on their own client or vendor id; the portal guard has no `records.view-all` permission at all.

---

## 6 · Field-level protection

Handled in the API Resource, not the Blade/React layer, so a field the user may not see is never serialised into the Inertia payload:

```php
'passport_number' => $this->when($request->user()->can('viewVipFields', $this->resource), fn () => $this->passport_number),
```

Groups: **vip** (passport, EID, DOB, dietary, allergies, VIP notes), **manifest** (guest identity data), **financial** (all amounts, hidden from Ops without `finance.view-amounts`), **bank** (vendor and crew bank details, Finance and Admin only).

---

## 7 · Tests written before the features (brief §12)

- One feature test per policy ability × role — the matrix above generated as a data provider, so a permission regression fails the build.
- One test per gate rule, asserting pass, block, block-message content, override path and the Override Register row.
- Ownership scope tests: a Sales user requesting another agent's client by ID receives 404 on `show`, `edit`, `update`, `destroy`, the search endpoint, and every relation endpoint.
- Field-level tests asserting the encrypted and VIP fields are absent from the JSON payload without the permission — asserting on the response body, not on the rendered screen.
- `record_access_logs` assertions on every VIP read and every PII export.
