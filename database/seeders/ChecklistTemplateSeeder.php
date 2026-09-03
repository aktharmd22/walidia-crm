<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChecklistTemplate;
use Illuminate\Database\Seeder;

/**
 * The company's method, written down.
 *
 * These are the steps from the charter flowchart — sections 8, 10 and 13 — in
 * the order they actually happen, from the operations meeting through to the
 * guest stepping aboard. Two of them are marked blocking, because the gate
 * engine reads checklist items by key: `safety_briefing` is what stands
 * between a guest and the passerelle, and `final_inspection` is what stands
 * between the yacht and the berth.
 *
 * Everything else is a prompt, not a barrier. A checklist that blocks on
 * twenty things is a checklist the crew learns to tick without reading.
 */
class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Charter operations — planning',
                'trigger' => 'booking.confirmed',
                'items' => [
                    // section, key, title, role, offset hours from departure, blocking, photo
                    ['planning', 'operations_manager_assigned', 'Operations manager assigned', 'operations', -168, false, false],
                    ['planning', 'client_call', 'Client call or meeting held', 'operations', -144, false, false],
                    ['planning', 'charter_details_verified', 'Charter details verified with the client', 'operations', -144, false, false],
                    ['planning', 'final_guest_requirements', 'Final guest requirements captured', 'operations', -120, false, false],
                    ['planning', 'operations_meeting', 'Operations meeting held', 'operations', -120, false, false],
                    ['planning', 'itinerary_finalised', 'Route and itinerary finalised', 'operations', -96, false, false],
                    ['planning', 'weather_check', 'Weather checked for the charter window', 'operations', -48, false, false],
                    ['planning', 'marina_coordination', 'Marina coordinated — berth and access confirmed', 'operations', -48, false, false],
                    ['planning', 'guest_manifest', 'Guest manifest prepared', 'operations', -24, false, false],
                    ['planning', 'special_requests_confirmed', 'Special requests confirmed with vendors', 'operations', -48, false, false],
                    ['planning', 'provisioning_ordered', 'Provisioning ordered', 'operations', -48, false, false],
                    ['planning', 'crew_briefing', 'Crew briefed on the charter', 'captain', -12, false, false],
                ],
            ],

            [
                'name' => 'Yacht preparation',
                'trigger' => 'charter.day_minus_one',
                'items' => [
                    ['preparation', 'inventory_check', 'Inventory and water toys checked', 'crew', -12, false, false],
                    ['preparation', 'pre_charter_checklist', 'Pre-charter checklist completed', 'captain', -6, false, false],
                    ['preparation', 'fuel_and_water', 'Fuel and water topped up', 'engineer', -6, false, false],
                    ['preparation', 'cleaning_complete', 'Yacht cleaned and dressed', 'crew', -4, false, true],
                    // Blocking: the yacht does not leave the berth on an unsigned inspection.
                    ['preparation', 'final_inspection', 'Final inspection signed off by the captain', 'captain', -2, true, true],
                ],
            ],

            [
                'name' => 'Charter day',
                'trigger' => 'charter.day',
                'items' => [
                    ['arrival', 'yacht_arrival', 'Yacht alongside at the pickup berth', 'captain', -2, false, false],
                    ['arrival', 'yacht_setup', 'Yacht set up — catering, décor, water toys', 'crew', -2, false, true],
                    ['arrival', 'service_provider_arrival', 'Service providers arrived and briefed', 'operations', -1, false, false],
                    ['arrival', 'guest_transfer', 'Guest transfer dispatched', 'operations', -1, false, false],
                    ['boarding', 'guest_arrival', 'Guests arrived at the marina', 'operations', 0, false, false],
                    ['boarding', 'guest_check_in', 'Guests checked in', 'operations', 0, false, false],
                    // Blocking: the boarding gate reads this key by name.
                    ['boarding', 'safety_briefing', 'Safety briefing delivered to all guests', 'captain', 0, true, false],
                    ['boarding', 'captain_introduction', 'Captain introduced to the lead guest', 'captain', 0, false, false],
                    ['closing', 'guest_check_out', 'Guests checked out', 'operations', 0, false, false],
                    ['closing', 'damage_inspection', 'Damage inspection carried out', 'captain', 0, false, true],
                    ['closing', 'yacht_handover', 'Yacht returned to berth and secured', 'captain', 0, false, false],
                ],
            ],
        ];

        foreach ($templates as $definition) {
            $template = ChecklistTemplate::updateOrCreate(
                ['name' => $definition['name']],
                ['business_line' => 'charter', 'trigger' => $definition['trigger'], 'is_active' => true],
            );

            $order = 0;

            foreach ($definition['items'] as [$section, $key, $title, $role, $offset, $blocking, $photo]) {
                $template->items()->updateOrCreate(
                    ['key' => $key],
                    [
                        'section' => $section,
                        'title_en' => $title,
                        'responsible_role' => $role,
                        'offset_hours' => $offset,
                        'requires_photo' => $photo,
                        'requires_signature' => $key === 'final_inspection',
                        'is_blocking' => $blocking,
                        'sort_order' => $order += 10,
                    ],
                );
            }
        }

        $this->command?->info('Seeded '.count($templates).' checklist templates.');
    }
}
