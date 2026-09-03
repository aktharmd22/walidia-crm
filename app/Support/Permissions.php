<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The canonical permission catalogue.
 *
 * One source of truth for the seeder, the policies, the settings UI and the
 * role-matrix feature test. Entities get the full CRUD verb set (D-018);
 * state transitions get their own verbs because each one is gate-evaluated
 * and must be grantable independently of "update".
 */
final class Permissions
{
    /** Verbs granted to every business entity. */
    public const CRUD = ['view', 'create', 'update', 'delete', 'restore', 'export', 'import'];

    /**
     * Business entities by domain. The key is the permission prefix and the
     * route/module name; keeping them identical is what lets the matrix test
     * assert that no controller action is missing a permission.
     *
     * @var array<string, list<string>>
     */
    public const ENTITIES = [
        'core' => ['users', 'roles', 'teams', 'settings', 'list-options', 'sequences', 'integrations'],

        'crm' => [
            'leads', 'lead-sources', 'clients', 'client-contacts', 'companies',
            'deals', 'pipelines', 'tasks', 'notes', 'activities', 'tags', 'saved-views',
        ],

        'fleet' => [
            'yachts', 'yacht-media', 'yacht-inventory', 'yacht-availability',
            'yacht-owners', 'owner-agreements', 'marinas', 'berths',
        ],

        'charter' => [
            'charter-enquiries', 'charter-matches', 'charter-proposals', 'bookings',
            'booking-guests', 'guest-manifests', 'cost-sheets', 'checklist-templates',
            'operations-checklists', 'charter-day-logs', 'charter-extras', 'incidents',
            'damage-reports', 'security-deposits', 'charter-feedback', 'cancellation-policies',
        ],

        'brokerage' => [
            'listing-agreements', 'listings', 'valuations', 'buyer-requirements', 'ndas',
            'viewings', 'offers', 'surveys', 'sea-trials', 'transactions', 'handovers', 'inspections',
            'aml-screenings',
        ],

        'operations' => [
            'crew', 'crew-documents', 'crew-assignments', 'crew-payouts',
            'vendors', 'vendor-categories', 'purchase-orders',
            'management-agreements', 'maintenance', 'maintenance-schedules', 'maintenance-logs',
            'certificates', 'owner-statements', 'maintenance-schedules',
        ],

        'finance' => [
            'quotations', 'invoices', 'payment-schedules', 'payments', 'receipts', 'refunds',
            'commissions', 'commission-rules', 'payouts', 'expenses',
            'vat-records', 'vat-rates', 'bank-charges', 'exchange-rates',
        ],

        'documents' => ['documents', 'document-templates', 'signature-requests'],

        'engine' => [
            'gate-rules', 'workflows', 'message-templates', 'reminder-rules',
            'communications', 'notifications', 'portal-invitations',
        ],
    ];

    /**
     * Entities that are read-only ledgers: they get `view` and `export` only,
     * because nothing in the application may write or amend them (D-008).
     *
     * @var list<string>
     */
    public const READ_ONLY = [
        'audits', 'gate-evaluations', 'gate-overrides', 'record-access-logs',
        'webhook-events', 'workflow-runs', 'imports', 'exports',
    ];

    /**
     * State transitions. Each is gate-evaluated, so each is its own permission.
     *
     * @var list<string>
     */
    public const TRANSITIONS = [
        'charter-proposals.send',
        'charter-proposals.accept',
        'bookings.hold',
        'bookings.generate-contract',
        'bookings.confirm',
        'bookings.release-operations',
        'bookings.board',
        'bookings.complete',
        'bookings.cancel',
        'cost-sheets.close',
        'damage-reports.close',
        'security-deposits.release',
        'crew-assignments.dispatch',
        'listings.activate',
        'listings.withdraw',
        'viewings.verify-buyer',
        'offers.submit',
        'offers.accept',
        'offers.reject',
        'transactions.transfer-ownership',
        'transactions.clear-aml',
        'handovers.complete',
        'maintenance-schedules.raise-job',
        'valuations.decide',
        'listings.publish',
        'viewings.schedule',
        'ndas.mark-signed',
        'purchase-orders.approve',
        'owner-statements.issue',
        'quotations.convert',
        'invoices.issue',
        'invoices.void',
        'invoices.credit-note',
        'payments.confirm-deposit',
        'payments.reconcile',
        'commissions.approve',
        'payouts.approve',
        'payouts.pay',
    ];

    /**
     * Cross-cutting permissions: visibility, field-level access, and the two
     * that exist to be audited — gate override and audit-log access.
     *
     * @var list<string>
     */
    public const CROSS_CUTTING = [
        'records.view-all',
        'records.view-team',
        'records.reassign',
        'clients.view-vip',
        'clients.export-pii',
        'finance.view-amounts',
        'gates.override',
        'compliance.verify-kyc',
        'compliance.view-audit',
        'settings.manage',
        'automation.manage',
        'line.charter',
        'line.brokerage',
    ];

    /**
     * Every permission name in the system.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::ENTITIES as $entities) {
            foreach ($entities as $entity) {
                foreach (self::CRUD as $verb) {
                    $permissions[] = "{$entity}.{$verb}";
                }
            }
        }

        foreach (self::READ_ONLY as $entity) {
            $permissions[] = "{$entity}.view";
            $permissions[] = "{$entity}.export";
        }

        $permissions = array_merge($permissions, self::TRANSITIONS, self::CROSS_CUTTING);

        sort($permissions);

        return array_values(array_unique($permissions));
    }

    /**
     * Flat list of every entity name (writable ones only).
     *
     * @return list<string>
     */
    public static function entities(): array
    {
        return array_merge(...array_values(self::ENTITIES));
    }

    /**
     * Expand a set of entity names into their CRUD permissions, optionally
     * limited to a subset of verbs.
     *
     * @param  list<string>  $entities
     * @param  list<string>  $verbs
     * @return list<string>
     */
    public static function crud(array $entities, array $verbs = self::CRUD): array
    {
        $permissions = [];

        foreach ($entities as $entity) {
            foreach ($verbs as $verb) {
                $permissions[] = "{$entity}.{$verb}";
            }
        }

        return $permissions;
    }
}
