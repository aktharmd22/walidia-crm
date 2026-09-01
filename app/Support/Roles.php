<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The four roles, and exactly what each one may do.
 *
 * This is the permission matrix from docs/phase-0/05-permissions.md expressed
 * as data. The role-matrix feature test reads this file, so a permission
 * granted here without a policy to back it fails the build.
 */
final class Roles
{
    public const SALES = 'sales';

    public const OPERATIONS = 'operations';

    public const FINANCE = 'finance';

    public const ADMIN = 'admin';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::SALES, self::OPERATIONS, self::FINANCE, self::ADMIN];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::SALES => 'Sales',
            self::OPERATIONS => 'Operations',
            self::FINANCE => 'Finance',
            self::ADMIN => 'Admin',
        ];
    }

    /**
     * Permissions granted to each role. Admin is handled separately: it holds
     * every permission, granted by a Gate::before check so new permissions are
     * never silently missing from it.
     *
     * @return array<string, list<string>>
     */
    public static function matrix(): array
    {
        return [
            self::SALES => array_merge(
                // Owns the client relationship and the deal, start to finish.
                Permissions::crud([
                    'leads', 'clients', 'client-contacts', 'companies', 'deals',
                    'charter-enquiries', 'charter-matches', 'charter-proposals', 'bookings',
                    'buyer-requirements', 'listings', 'listing-agreements', 'valuations',
                    'ndas', 'viewings', 'offers', 'charter-feedback',
                ]),
                Permissions::crud([
                    'tasks', 'notes', 'activities', 'tags', 'saved-views', 'communications',
                    'documents', 'signature-requests',
                ]),
                // Reads the operational and financial context of its own deals.
                Permissions::crud(
                    ['yachts', 'yacht-media', 'yacht-availability', 'marinas', 'cost-sheets',
                        'quotations', 'invoices', 'payments', 'surveys', 'sea-trials', 'transactions',
                        'booking-guests', 'operations-checklists', 'commissions', 'lead-sources',
                        'cancellation-policies', 'notifications'],
                    ['view'],
                ),
                // Quoted-phase pricing is Sales work; invoiced and actual are not.
                ['cost-sheets.create', 'cost-sheets.update', 'cost-sheets.export'],
                [
                    'charter-proposals.send', 'charter-proposals.accept',
                    'bookings.hold', 'bookings.generate-contract', 'bookings.confirm', 'bookings.cancel',
                    'listings.activate', 'listings.withdraw', 'viewings.verify-buyer',
                    'offers.submit', 'offers.accept', 'offers.reject', 'quotations.convert',
                ],
                ['line.charter', 'line.brokerage'],
            ),

            self::OPERATIONS => array_merge(
                Permissions::crud([
                    'yachts', 'yacht-media', 'yacht-inventory', 'yacht-availability', 'marinas', 'berths',
                    'checklist-templates', 'operations-checklists', 'charter-day-logs', 'charter-extras',
                    'incidents', 'damage-reports', 'booking-guests', 'guest-manifests',
                    'crew', 'crew-documents', 'crew-assignments',
                    'vendors', 'vendor-categories', 'purchase-orders',
                    'maintenance-schedules', 'maintenance-logs', 'certificates',
                    'handovers', 'management-agreements',
                ]),
                Permissions::crud(['tasks', 'notes', 'activities', 'documents', 'saved-views', 'communications']),
                // Sees the bookings it must deliver, not the money behind them.
                Permissions::crud(
                    ['bookings', 'clients', 'companies', 'charter-enquiries', 'listings',
                        'owner-agreements', 'yacht-owners', 'notifications'],
                    ['view'],
                ),
                ['bookings.board', 'bookings.complete', 'damage-reports.close',
                    'crew-assignments.dispatch', 'purchase-orders.approve'],
                ['records.view-all'],
            ),

            self::FINANCE => array_merge(
                Permissions::crud([
                    'quotations', 'invoices', 'payment-schedules', 'payments', 'receipts', 'refunds',
                    'commissions', 'commission-rules', 'payouts', 'expenses',
                    'vat-records', 'vat-rates', 'bank-charges', 'exchange-rates',
                    'cost-sheets', 'security-deposits', 'crew-payouts', 'owner-statements',
                ]),
                Permissions::crud(['tasks', 'notes', 'documents', 'saved-views']),
                Permissions::crud(
                    ['clients', 'companies', 'bookings', 'deals', 'charter-proposals', 'listings',
                        'transactions', 'purchase-orders', 'vendors', 'crew', 'yachts',
                        'owner-agreements', 'activities', 'communications', 'notifications'],
                    ['view'],
                ),
                [
                    'invoices.issue', 'invoices.void', 'invoices.credit-note',
                    'payments.confirm-deposit', 'payments.reconcile',
                    'bookings.release-operations', 'security-deposits.release', 'cost-sheets.close',
                    'commissions.approve', 'payouts.approve', 'payouts.pay',
                    'owner-statements.issue', 'transactions.transfer-ownership',
                ],
                ['records.view-all', 'finance.view-amounts', 'compliance.view-audit'],
            ),

            self::ADMIN => ['*'],
        ];
    }

    /**
     * Permissions granted per role, resolved against the catalogue so a typo
     * in the matrix above fails the seeder rather than silently granting nothing.
     *
     * @return list<string>
     */
    public static function permissionsFor(string $role): array
    {
        if ($role === self::ADMIN) {
            return Permissions::all();
        }

        $granted = self::matrix()[$role] ?? [];
        $known = Permissions::all();

        $unknown = array_diff($granted, $known);

        if ($unknown !== []) {
            throw new \InvalidArgumentException(
                "Role [{$role}] grants unknown permissions: ".implode(', ', $unknown),
            );
        }

        return array_values(array_unique($granted));
    }
}
