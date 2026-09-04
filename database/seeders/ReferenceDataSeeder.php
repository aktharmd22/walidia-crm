<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LeadSource;
use App\Models\ListOption;
use App\Models\Marina;
use App\Models\Setting;
use App\Support\Statuses;
use Illuminate\Database\Seeder;

/**
 * Reference data the business runs on from day one: the Gulf marinas Walidia
 * actually sails from, lead sources, and the dropdown lists that would
 * otherwise be typed differently by every user.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $marinas = [
            ['Yas Marina', 'Abu Dhabi', 'Abu Dhabi', 'United Arab Emirates', 'Asia/Dubai', 24.4672, 54.6031, true],
            ['Al Bateen Marina', 'Abu Dhabi', 'Abu Dhabi', 'United Arab Emirates', 'Asia/Dubai', 24.4467, 54.3272, true],
            ['Zayed Port Marina', 'Abu Dhabi', 'Abu Dhabi', 'United Arab Emirates', 'Asia/Dubai', 24.5150, 54.3800, false],
            ['Dubai Harbour', 'Dubai', 'Dubai', 'United Arab Emirates', 'Asia/Dubai', 25.0930, 55.1440, true],
            ['Dubai Marina Yacht Club', 'Dubai', 'Dubai', 'United Arab Emirates', 'Asia/Dubai', 25.0780, 55.1400, true],
            ['Port Rashid Marina', 'Dubai', 'Dubai', 'United Arab Emirates', 'Asia/Dubai', 25.2700, 55.2750, false],
            ['Lusail Marina', 'Lusail', null, 'Qatar', 'Asia/Qatar', 25.3700, 51.5300, true],
            ['Marina Bandar Al Rowdha', 'Muscat', null, 'Oman', 'Asia/Muscat', 23.5750, 58.6100, true],
            ['Eden Island Marina', 'Mahé', null, 'Seychelles', 'Indian/Mahe', -4.6300, 55.4800, true],
            ['Hulhumalé Marina', 'Malé', null, 'Maldives', 'Indian/Maldives', 4.2100, 73.5400, true],
        ];

        foreach ($marinas as [$name, $city, $emirate, $country, $timezone, $latitude, $longitude, $manifest]) {
            Marina::updateOrCreate(
                ['name' => $name],
                [
                    'city' => $city,
                    'emirate' => $emirate,
                    'country' => $country,
                    'timezone' => $timezone,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'requires_manifest' => $manifest,
                    'manifest_format' => $manifest ? 'pdf' : null,
                    'is_active' => true,
                ],
            );
        }

        foreach ([
            ['Website enquiry', 'website'],
            ['WhatsApp', 'whatsapp'],
            ['Phone call', 'phone'],
            ['Email', 'email'],
            ['Referral — existing client', 'referral'],
            ['Referral — partner or DMC', 'referral'],
            ['Instagram', 'social'],
            ['Boat show', 'event'],
            ['Walk-in at the marina', 'walk_in'],
            ['Repeat client', 'direct'],
        ] as [$name, $channel]) {
            LeadSource::updateOrCreate(['name' => $name], ['channel' => $channel, 'is_active' => true]);
        }

        $lists = [
            'experience_type' => Statuses::EXPERIENCE,
            'occasion' => [
                'birthday' => 'Birthday',
                'anniversary' => 'Anniversary',
                'family' => 'Family day',
                'business' => 'Business entertaining',
                'national_day' => 'National Day',
                'other' => 'Other',
            ],
            'incident_category' => [
                'guest_injury' => 'Guest injury',
                'crew_injury' => 'Crew injury',
                'grounding' => 'Grounding',
                'collision' => 'Collision',
                'mechanical' => 'Mechanical failure',
                'weather' => 'Weather',
                'security' => 'Security',
                'other' => 'Other',
            ],
            /*
             * The Cost and Offer Table from the charter flowchart §7. The cost
             * sheet takes any category, but these are the ones the business
             * actually keeps — so they are seeded rather than typed fresh on
             * every charter, and a quoted-to-actual variance compares like
             * with like.
             */
            'cost_category_revenue' => [
                'hourly_rate' => 'Hourly rate',
                'yacht_fee' => 'Yacht fees',
                'visitor_fee' => 'Visitor fees',
                'berth_fee' => 'Berth fees',
                'security_deposit' => 'Security deposit',
                'food' => 'Food',
                'beverages' => 'Beverages',
                'entertainment' => 'Entertainment',
                'watersports' => 'Watersports',
                'guest_transfer' => 'Guest transfer',
                'additional' => 'Additional',
                'other_revenue' => 'Other',
            ],
            'cost_category_cost' => [
                'standard_inclusions' => 'Standard inclusions',
                'operations_staff' => 'Operations staff',
                'buggy_driver_tips' => 'Buggy driver tips',
                'catering_tips' => 'Catering tips',
                'crew_tips' => 'Crew tips',
                'team_commission' => 'Team commission',
                'agent_commission' => 'Agent commission',
                'bank_charges' => 'Bank charges',
                'refund_apa' => 'Refund APA',
                'other_cost' => 'Other',
            ],
            'client_type' => Statuses::CLIENT_TYPE,
            'vendor_category' => [
                'catering' => 'Catering',
                'watersports' => 'Watersports',
                'transfers' => 'Transfers',
                'flowers' => 'Flowers and décor',
                'photography' => 'Photography',
                'fuel' => 'Fuel and bunkering',
                'technical' => 'Technical and engineering',
                'cleaning' => 'Cleaning and laundry',
                'entertainment' => 'Entertainment',
                'videography' => 'Videography',
                'hotel' => 'Hotel and accommodation',
                'security' => 'Security',
            ],
        ];

        foreach ($lists as $listKey => $options) {
            $order = 0;

            foreach ($options as $value => $label) {
                ListOption::updateOrCreate(
                    ['list_key' => $listKey, 'value' => $value],
                    ['label_en' => $label, 'sort_order' => $order++, 'is_active' => true, 'is_system' => true],
                );
            }
        }

        // Company profile — the values that appear on every tax invoice.
        Setting::put('company', 'legal_name', 'Walidia Yachts LLC');
        Setting::put('company', 'trade_name', 'Walidia Yachts');
        Setting::put('company', 'city', 'Abu Dhabi');
        Setting::put('company', 'country', 'United Arab Emirates');
        Setting::put('company', 'currency', 'AED');
        Setting::put('tax', 'default_rate', 5);
        Setting::put('tax', 'trn', null);

        $this->command->info('Seeded 10 marinas, 8 lead sources, 4 lists and the company profile.');
    }
}
