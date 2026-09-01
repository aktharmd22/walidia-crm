<?php

declare(strict_types=1);

/**
 * Walidia platform configuration.
 *
 * Business values that vary by client, tax rule or region live here rather
 * than in code. Nothing in this file is a hardcoded assumption the business
 * cannot change without a deployment.
 */
return [

    /* Display timezone. Storage is always UTC (D-010). */
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Dubai'),

    'currency' => env('APP_CURRENCY', 'AED'),

    'currencies' => ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'OMR'],

    'locales' => ['en', 'ar'],

    /* Security */
    'require_two_factor' => (bool) env('REQUIRE_TWO_FACTOR', true),
    'password_min_length' => 12,
    'session_idle_minutes' => (int) env('SESSION_LIFETIME', 480),
    'signed_link_days' => 7,
    'document_url_ttl_minutes' => 5,

    /* Tax — never hardcode 5% in a calculation (Q5). */
    'tax' => [
        'default_rate' => (float) env('APP_VAT_RATE', 5),
        'treatments' => ['standard', 'zero_rated', 'out_of_scope', 'reverse_charge'],
        'out_of_scope_categories' => ['security_deposit', 'crew_tips', 'catering_tips', 'buggy_driver_tips'],
    ],

    /* Gate engine */
    'gates' => [
        'cache_seconds' => 300,
        'override_reason_min_length' => 20,
        'certificate_types_for_dispatch' => ['registration', 'insurance', 'safety'],
        'soft_warning_days' => 30,
    ],

    /* Thresholds the business will want to tune (Q24). */
    'aml' => [
        'threshold' => (float) env('AML_THRESHOLD', 1000000),
        'currency' => 'AED',
    ],

    /* Integrations run on fakes until credentials exist (D-016). */
    'drivers' => [
        'whatsapp' => env('WHATSAPP_DRIVER', 'fake'),
        'esign' => env('ESIGN_DRIVER', 'fake'),
        'payments' => env('PAYMENTS_DRIVER', 'fake'),
        'weather' => env('WEATHER_DRIVER', 'fake'),
        'website_sync' => env('WEBSITE_SYNC_DRIVER', 'fake'),
    ],

    /* Human-facing identifier formats (D-013, Q8). */
    'sequences' => [
        'client' => ['prefix' => 'CL', 'period' => 'yearly', 'padding' => 4],
        'lead' => ['prefix' => 'LD', 'period' => 'yearly', 'padding' => 4],
        'enquiry' => ['prefix' => 'EN', 'period' => 'yearly', 'padding' => 4],
        'proposal' => ['prefix' => 'PR', 'period' => 'yearly', 'padding' => 4],
        'booking' => ['prefix' => 'BK', 'period' => 'yearly', 'padding' => 4],
        'invoice' => ['prefix' => 'INV', 'period' => 'yearly', 'padding' => 5],
        'credit_note' => ['prefix' => 'CN', 'period' => 'yearly', 'padding' => 5],
        'receipt' => ['prefix' => 'RC', 'period' => 'yearly', 'padding' => 5],
        'quotation' => ['prefix' => 'QT', 'period' => 'yearly', 'padding' => 4],
        'purchase_order' => ['prefix' => 'PO', 'period' => 'yearly', 'padding' => 4],
        'listing' => ['prefix' => 'LS', 'period' => 'yearly', 'padding' => 4],
        'offer' => ['prefix' => 'OF', 'period' => 'yearly', 'padding' => 4],
        'transaction' => ['prefix' => 'TR', 'period' => 'yearly', 'padding' => 4],
        'owner_statement' => ['prefix' => 'OS', 'period' => 'yearly', 'padding' => 4],
        'yacht' => ['prefix' => 'YT', 'period' => 'none', 'padding' => 4],
        'crew' => ['prefix' => 'CR', 'period' => 'none', 'padding' => 4],
        'vendor' => ['prefix' => 'VN', 'period' => 'none', 'padding' => 4],
        'incident' => ['prefix' => 'IN', 'period' => 'yearly', 'padding' => 4],
        'damage' => ['prefix' => 'DR', 'period' => 'yearly', 'padding' => 4],
        'document' => ['prefix' => 'DOC', 'period' => 'yearly', 'padding' => 5],
        'task' => ['prefix' => 'TK', 'period' => 'yearly', 'padding' => 5],
        'deal' => ['prefix' => 'DL', 'period' => 'yearly', 'padding' => 4],
        'nda' => ['prefix' => 'NDA', 'period' => 'yearly', 'padding' => 4],
        'viewing' => ['prefix' => 'VW', 'period' => 'yearly', 'padding' => 4],
        'survey' => ['prefix' => 'SV', 'period' => 'yearly', 'padding' => 4],
        'management' => ['prefix' => 'MG', 'period' => 'yearly', 'padding' => 4],
        'certificate' => ['prefix' => 'CT', 'period' => 'none', 'padding' => 4],
        'maintenance' => ['prefix' => 'MT', 'period' => 'yearly', 'padding' => 4],
        'statement' => ['prefix' => 'OS', 'period' => 'yearly', 'padding' => 4],
        'buyer-req' => ['prefix' => 'BR', 'period' => 'yearly', 'padding' => 4],
        'handover' => ['prefix' => 'HO', 'period' => 'yearly', 'padding' => 4],
        'payout' => ['prefix' => 'PY', 'period' => 'yearly', 'padding' => 4],
        'commission' => ['prefix' => 'CM', 'period' => 'yearly', 'padding' => 5],
        'cost_sheet' => ['prefix' => 'CS', 'period' => 'yearly', 'padding' => 4],
        'checklist' => ['prefix' => 'CK', 'period' => 'yearly', 'padding' => 4],
        'refund' => ['prefix' => 'RF', 'period' => 'yearly', 'padding' => 4],
        'expense' => ['prefix' => 'EX', 'period' => 'yearly', 'padding' => 5],
        'management_agreement' => ['prefix' => 'MA', 'period' => 'yearly', 'padding' => 4],
        'owner_agreement' => ['prefix' => 'OA', 'period' => 'yearly', 'padding' => 4],
        'listing_agreement' => ['prefix' => 'LA', 'period' => 'yearly', 'padding' => 4],
        'aml' => ['prefix' => 'AML', 'period' => 'yearly', 'padding' => 4],
    ],

    /* Charter commercial defaults — placeholders until Q10 is answered. */
    'charter' => [
        'deposit_percentage' => 50,
        'balance_due_days_before' => 7,
        'apa_percentage' => 25,
        'security_deposit_release_hours' => 72,
    ],

    /* Commission defaults — placeholders until Q4 is answered. */
    'commission' => [
        'team_basis' => 'profit',      // profit | offer
        'team_rate' => 5.0,
        'agent_basis' => 'offer',
        'agent_rate' => 10.0,
        'brokerage_rate' => 10.0,
        'co_broker_split' => 50.0,
    ],
];
