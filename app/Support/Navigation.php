<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * The sidebar, defined once on the server.
 *
 * Items are filtered two ways: by permission, and by whether the route exists
 * yet. The second filter means the navigation always reflects what is actually
 * built — a phase that has not landed does not leave a dead link in the shell.
 */
final class Navigation
{
    /**
     * @return list<array{key: string, group: string, label: string, icon: string, href: string|null, children: list<array{key: string, label: string, href: string}>}>
     */
    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $sections = [];

        foreach (self::tree() as $section) {
            $children = [];

            foreach ($section['children'] ?? [] as $child) {
                if (! self::visible($user, $child)) {
                    continue;
                }

                $children[] = [
                    'key' => $child['key'],
                    'label' => $child['label'],
                    'href' => route($child['route']),
                    'badge' => null,
                ];
            }

            $sectionVisible = isset($section['route'])
                && self::visible($user, $section);

            if ($children === [] && ! $sectionVisible) {
                continue;
            }

            $sections[] = [
                'key' => $section['key'],
                'group' => $section['group'] ?? 'Overview',
                'label' => $section['label'],
                'icon' => $section['icon'],
                'href' => $sectionVisible ? route($section['route']) : null,
                'children' => $children,
            ];
        }

        return $sections;
    }

    /**
     * @param  array{route?: string, permission?: string}  $item
     */
    private static function visible(User $user, array $item): bool
    {
        if (! isset($item['route']) || ! Route::has($item['route'])) {
            return false;
        }

        if (isset($item['permission']) && ! $user->can($item['permission'])) {
            return false;
        }

        return true;
    }

    /**
     * The full tree from the brief. Routes that do not exist yet are simply
     * skipped, so this can be written once and revealed phase by phase.
     *
     * @return list<array<string, mixed>>
     */
    public static function tree(): array
    {
        return [
            [
                'key' => 'dashboard', 'group' => 'Overview', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard',
                'children' => [],
            ],
            [
                'key' => 'leads', 'group' => 'CRM', 'label' => 'Leads', 'icon' => 'leads', 'route' => 'leads.index', 'permission' => 'leads.view',
                'children' => [
                    ['key' => 'leads-inbox', 'label' => 'Inbox', 'route' => 'leads.inbox', 'permission' => 'leads.view'],
                    ['key' => 'leads-all', 'label' => 'All Leads', 'route' => 'leads.index', 'permission' => 'leads.view'],
                    ['key' => 'leads-followup', 'label' => 'Follow-Up Pool', 'route' => 'leads.follow-up', 'permission' => 'leads.view'],
                    ['key' => 'leads-duplicates', 'label' => 'Duplicates', 'route' => 'leads.duplicates', 'permission' => 'leads.view'],
                ],
            ],
            [
                'key' => 'clients', 'group' => 'CRM', 'label' => 'Clients', 'icon' => 'clients', 'route' => 'clients.index', 'permission' => 'clients.view',
                'children' => [
                    ['key' => 'clients-all', 'label' => 'All', 'route' => 'clients.index', 'permission' => 'clients.view'],
                    ['key' => 'clients-companies', 'label' => 'Companies', 'route' => 'companies.index', 'permission' => 'companies.view'],
                    ['key' => 'clients-vip', 'label' => 'VIP Register', 'route' => 'clients.vip', 'permission' => 'clients.view-vip'],
                    ['key' => 'clients-owners', 'label' => 'Owners', 'route' => 'clients.owners', 'permission' => 'clients.view'],
                    ['key' => 'clients-approval', 'label' => 'Approval Queue', 'route' => 'clients.approval-queue', 'permission' => 'compliance.verify-kyc'],
                ],
            ],
            [
                'key' => 'charter', 'group' => 'Business lines', 'label' => 'Charter', 'icon' => 'charter', 'route' => 'charter.enquiries.index', 'permission' => 'charter-enquiries.view',
                'children' => [
                    ['key' => 'charter-enquiries', 'label' => 'Enquiries', 'route' => 'charter.enquiries.index', 'permission' => 'charter-enquiries.view'],
                    ['key' => 'charter-proposals', 'label' => 'Proposals', 'route' => 'charter.proposals.index', 'permission' => 'charter-proposals.view'],
                    ['key' => 'charter-bookings', 'label' => 'Bookings', 'route' => 'charter.bookings.index', 'permission' => 'bookings.view'],
                    ['key' => 'charter-calendar', 'label' => 'Calendar', 'route' => 'charter.calendar', 'permission' => 'bookings.view'],
                    ['key' => 'charter-cost-sheets', 'label' => 'Cost Sheets', 'route' => 'charter.cost-sheets.index', 'permission' => 'cost-sheets.view'],
                    ['key' => 'charter-checklists', 'label' => 'Checklists', 'route' => 'charter.checklists.index', 'permission' => 'operations-checklists.view'],
                    ['key' => 'charter-day', 'label' => 'Charter Day', 'route' => 'charter.day.index', 'permission' => 'charter-day-logs.view'],
                    ['key' => 'charter-incidents', 'label' => 'Incidents', 'route' => 'charter.incidents.index', 'permission' => 'incidents.view'],
                    ['key' => 'charter-pnl', 'label' => 'Charter P&L', 'route' => 'charter.pnl', 'permission' => 'finance.view-amounts'],
                ],
            ],
            [
                'key' => 'brokerage', 'group' => 'Business lines', 'label' => 'Brokerage', 'icon' => 'brokerage', 'route' => 'brokerage.listings.index', 'permission' => 'listings.view',
                'children' => [
                    ['key' => 'brokerage-buyers', 'label' => 'Buyer Requirements', 'route' => 'brokerage.buyer-requirements.index', 'permission' => 'buyer-requirements.view'],
                    ['key' => 'brokerage-listings', 'label' => 'Listings', 'route' => 'brokerage.listings.index', 'permission' => 'listings.view'],
                    ['key' => 'brokerage-agreements', 'label' => 'Agreements', 'route' => 'brokerage.listing-agreements.index', 'permission' => 'listing-agreements.view'],
                    ['key' => 'brokerage-viewings', 'label' => 'Viewings', 'route' => 'brokerage.viewings.index', 'permission' => 'viewings.view'],
                    ['key' => 'brokerage-offers', 'label' => 'Offers', 'route' => 'brokerage.offers.index', 'permission' => 'offers.view'],
                    ['key' => 'brokerage-surveys', 'label' => 'Surveys', 'route' => 'brokerage.surveys.index', 'permission' => 'surveys.view'],
                    ['key' => 'brokerage-transactions', 'label' => 'Transactions', 'route' => 'brokerage.transactions.index', 'permission' => 'transactions.view'],
                    ['key' => 'brokerage-handover', 'label' => 'Handover', 'route' => 'brokerage.handovers.index', 'permission' => 'handovers.view'],
                ],
            ],
            [
                'key' => 'management', 'group' => 'Business lines', 'label' => 'Management', 'icon' => 'management', 'route' => 'management.agreements.index', 'permission' => 'management-agreements.view',
                'children' => [
                    ['key' => 'management-agreements', 'label' => 'Agreements', 'route' => 'management.agreements.index', 'permission' => 'management-agreements.view'],
                    ['key' => 'management-maintenance', 'label' => 'Maintenance', 'route' => 'management.maintenance.index', 'permission' => 'maintenance.view'],
                    ['key' => 'management-certificates', 'label' => 'Certificates', 'route' => 'management.certificates.index', 'permission' => 'certificates.view'],
                    ['key' => 'management-po', 'label' => 'Purchase Orders', 'route' => 'management.purchase-orders.index', 'permission' => 'purchase-orders.view'],
                    ['key' => 'management-statements', 'label' => 'Owner Financials', 'route' => 'management.owner-statements.index', 'permission' => 'owner-statements.view'],
                ],
            ],
            [
                'key' => 'fleet', 'group' => 'Operations', 'label' => 'Fleet', 'icon' => 'fleet', 'route' => 'fleet.yachts.index', 'permission' => 'yachts.view',
                'children' => [
                    ['key' => 'fleet-yachts', 'label' => 'All Yachts', 'route' => 'fleet.yachts.index', 'permission' => 'yachts.view'],
                    ['key' => 'fleet-charter', 'label' => 'Charter Fleet', 'route' => 'fleet.charter-fleet', 'permission' => 'yachts.view'],
                    ['key' => 'fleet-sale', 'label' => 'For Sale', 'route' => 'fleet.for-sale', 'permission' => 'yachts.view'],
                    ['key' => 'fleet-availability', 'label' => 'Availability', 'route' => 'fleet.availability', 'permission' => 'yacht-availability.view'],
                    ['key' => 'fleet-marinas', 'label' => 'Marinas', 'route' => 'fleet.marinas.index', 'permission' => 'marinas.view'],
                ],
            ],
            [
                'key' => 'crew', 'group' => 'Operations', 'label' => 'Crew', 'icon' => 'crew', 'route' => 'crew.index', 'permission' => 'crew.view',
                'children' => [
                    ['key' => 'crew-directory', 'label' => 'Directory', 'route' => 'crew.index', 'permission' => 'crew.view'],
                    ['key' => 'crew-assignments', 'label' => 'Assignments', 'route' => 'crew.assignments.index', 'permission' => 'crew-assignments.view'],
                    ['key' => 'crew-expiry', 'label' => 'Expiry', 'route' => 'crew.expiry', 'permission' => 'crew-documents.view'],
                    ['key' => 'crew-payouts', 'label' => 'Payouts', 'route' => 'crew.payouts.index', 'permission' => 'crew-payouts.view'],
                ],
            ],
            [
                'key' => 'vendors', 'group' => 'Operations', 'label' => 'Vendors', 'icon' => 'vendors', 'route' => 'vendors.index', 'permission' => 'vendors.view',
                'children' => [
                    ['key' => 'vendors-directory', 'label' => 'Directory', 'route' => 'vendors.index', 'permission' => 'vendors.view'],
                    ['key' => 'vendors-categories', 'label' => 'Categories', 'route' => 'vendors.categories.index', 'permission' => 'vendor-categories.view'],
                    ['key' => 'vendors-po', 'label' => 'Purchase Orders', 'route' => 'management.purchase-orders.index', 'permission' => 'purchase-orders.view'],
                ],
            ],
            [
                'key' => 'finance', 'group' => 'Finance', 'label' => 'Finance', 'icon' => 'finance', 'route' => 'finance.invoices.index', 'permission' => 'invoices.view',
                'children' => [
                    ['key' => 'finance-quotations', 'label' => 'Quotations', 'route' => 'finance.quotations.index', 'permission' => 'quotations.view'],
                    ['key' => 'finance-invoices', 'label' => 'Invoices', 'route' => 'finance.invoices.index', 'permission' => 'invoices.view'],
                    ['key' => 'finance-schedules', 'label' => 'Schedules', 'route' => 'finance.payment-schedules.index', 'permission' => 'payment-schedules.view'],
                    ['key' => 'finance-payments', 'label' => 'Payments', 'route' => 'finance.payments.index', 'permission' => 'payments.view'],
                    ['key' => 'finance-overdue', 'label' => 'Overdue', 'route' => 'finance.overdue', 'permission' => 'invoices.view'],
                    ['key' => 'finance-deposits', 'label' => 'Deposits', 'route' => 'finance.security-deposits.index', 'permission' => 'security-deposits.view'],
                    ['key' => 'finance-commissions', 'label' => 'Commissions', 'route' => 'finance.commissions.index', 'permission' => 'commissions.view'],
                    ['key' => 'finance-payouts', 'label' => 'Payouts', 'route' => 'finance.payouts.index', 'permission' => 'payouts.view'],
                    ['key' => 'finance-vat', 'label' => 'VAT', 'route' => 'finance.vat-records.index', 'permission' => 'vat-records.view'],
                    ['key' => 'finance-pnl', 'label' => 'P&L', 'route' => 'finance.pnl', 'permission' => 'finance.view-amounts'],
                ],
            ],
            [
                'key' => 'documents', 'group' => 'Finance', 'label' => 'Documents', 'icon' => 'documents', 'route' => 'documents.index', 'permission' => 'documents.view',
                'children' => [
                    ['key' => 'documents-vault', 'label' => 'Vault', 'route' => 'documents.index', 'permission' => 'documents.view'],
                    ['key' => 'documents-templates', 'label' => 'Templates', 'route' => 'documents.templates.index', 'permission' => 'document-templates.view'],
                    ['key' => 'documents-signature', 'label' => 'Pending Signature', 'route' => 'documents.pending-signature', 'permission' => 'signature-requests.view'],
                    ['key' => 'documents-expiry', 'label' => 'Expiry', 'route' => 'documents.expiry', 'permission' => 'documents.view'],
                ],
            ],
            [
                'key' => 'compliance', 'group' => 'Operations', 'label' => 'Compliance', 'icon' => 'compliance', 'route' => 'compliance.kyc-queue', 'permission' => 'compliance.verify-kyc',
                'children' => [
                    ['key' => 'compliance-kyc', 'label' => 'KYC Queue', 'route' => 'compliance.kyc-queue', 'permission' => 'compliance.verify-kyc'],
                    ['key' => 'compliance-certificates', 'label' => 'Certificates', 'route' => 'compliance.certificates.index', 'permission' => 'certificates.view'],
                    ['key' => 'compliance-audit', 'label' => 'Audit Log', 'route' => 'compliance.audit-log', 'permission' => 'compliance.view-audit'],
                    ['key' => 'compliance-overrides', 'label' => 'Override Register', 'route' => 'compliance.overrides', 'permission' => 'compliance.view-audit'],
                ],
            ],
            [
                'key' => 'communications', 'group' => 'Admin', 'label' => 'Communications', 'icon' => 'communications', 'route' => 'communications.index', 'permission' => 'communications.view',
                'children' => [
                    ['key' => 'communications-log', 'label' => 'Log', 'route' => 'communications.index', 'permission' => 'communications.view'],
                    ['key' => 'communications-templates', 'label' => 'Templates', 'route' => 'communications.templates.index', 'permission' => 'message-templates.view'],
                ],
            ],
            [
                'key' => 'automation', 'group' => 'Admin', 'label' => 'Automation', 'icon' => 'automation', 'route' => 'automation.gate-rules.index', 'permission' => 'automation.manage',
                'children' => [
                    ['key' => 'automation-workflows', 'label' => 'Workflows', 'route' => 'automation.workflows.index', 'permission' => 'workflows.view'],
                    ['key' => 'automation-gates', 'label' => 'Gate Rules', 'route' => 'automation.gate-rules.index', 'permission' => 'gate-rules.view'],
                    ['key' => 'automation-reminders', 'label' => 'Reminders', 'route' => 'automation.reminder-rules.index', 'permission' => 'reminder-rules.view'],
                ],
            ],
            [
                'key' => 'tasks', 'group' => 'Overview', 'label' => 'Tasks', 'icon' => 'tasks', 'route' => 'tasks.index', 'permission' => 'tasks.view',
                'children' => [
                    ['key' => 'tasks-mine', 'label' => 'My Tasks', 'route' => 'tasks.index', 'permission' => 'tasks.view'],
                    ['key' => 'tasks-team', 'label' => 'Team Tasks', 'route' => 'tasks.team', 'permission' => 'records.view-team'],
                    ['key' => 'tasks-overdue', 'label' => 'Overdue', 'route' => 'tasks.overdue', 'permission' => 'tasks.view'],
                ],
            ],
            [
                'key' => 'reports', 'group' => 'Finance', 'label' => 'Reports', 'icon' => 'reports', 'route' => 'reports.index',
                'children' => [
                    ['key' => 'reports-sales', 'label' => 'Sales', 'route' => 'reports.sales', 'permission' => 'deals.view'],
                    ['key' => 'reports-utilisation', 'label' => 'Utilisation', 'route' => 'reports.utilisation', 'permission' => 'yachts.view'],
                    ['key' => 'reports-commissions', 'label' => 'Commissions', 'route' => 'reports.commissions', 'permission' => 'commissions.view'],
                ],
            ],
            [
                'key' => 'settings', 'group' => 'Admin', 'label' => 'Settings', 'icon' => 'settings', 'route' => 'settings.users.index', 'permission' => 'settings.manage',
                'children' => [
                    ['key' => 'settings-users', 'label' => 'Users', 'route' => 'settings.users.index', 'permission' => 'users.view'],
                    ['key' => 'settings-roles', 'label' => 'Roles', 'route' => 'settings.roles.index', 'permission' => 'roles.view'],
                    ['key' => 'settings-lists', 'label' => 'Lists', 'route' => 'settings.list-options.index', 'permission' => 'list-options.view'],
                    ['key' => 'settings-company', 'label' => 'Company & TRN', 'route' => 'settings.company', 'permission' => 'settings.manage'],
                ],
            ],
        ];
    }
}
