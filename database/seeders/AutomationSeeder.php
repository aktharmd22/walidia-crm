<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\WorkflowRule;
use Illuminate\Database\Seeder;

/**
 * Every automation named in the two flowcharts, as data.
 *
 * Charter §19 lists them by department — sales, operations, finance, client —
 * and brokerage §10 adds the post-sale sequence. They are seeded rather than
 * coded so the timings are the operations manager's to change: moving the
 * charter reminder from 24 hours to 48 is an edit, not a deployment.
 */
class AutomationSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            /* Client — charter */
            ['booking_confirmation', 'Booking confirmation', 'email', 'Your charter is confirmed — {{reference}}',
                "Dear {{client_name}},\n\nYour charter aboard {{yacht_name}} on {{charter_date}} is confirmed.\n\nWe will be in touch closer to the day with the final details. If anything changes in the meantime, reply to this message and we will take care of it.\n\n{{company_name}}"],
            ['charter_reminder', 'Charter reminder', 'whatsapp', null,
                'Good day {{client_name}} — a reminder that your charter aboard {{yacht_name}} is tomorrow, boarding at {{charter_time}}. We look forward to welcoming you.'],
            ['weather_update', 'Weather update', 'whatsapp', null,
                '{{client_name}}, a note on conditions for {{charter_date}}: our captain has reviewed the forecast and will call you if anything needs adjusting. Nothing for you to do.'],
            ['thank_you', 'Thank you', 'email', 'Thank you for chartering with us',
                "Dear {{client_name}},\n\nThank you for joining us aboard {{yacht_name}}. It was a pleasure to have you.\n\nIf anything fell short, tell us plainly — we would rather hear it from you than read it elsewhere.\n\n{{company_name}}"],
            ['feedback_request', 'Feedback request', 'email', 'How was your charter?',
                "Dear {{client_name}},\n\nWe would value a few words on your charter aboard {{yacht_name}}. It takes a minute and it shapes what we do next.\n\n{{company_name}}"],
            ['review_request', 'Review request', 'email', 'Would you share your experience?',
                "Dear {{client_name}},\n\nIf your day aboard {{yacht_name}} was what you hoped for, a public review helps other guests find us.\n\n{{company_name}}"],
            ['repeat_charter', 'Repeat charter reminder', 'email', 'The season is opening again',
                "Dear {{client_name}},\n\nIt has been a while since your charter aboard {{yacht_name}}. The calendar for the coming season is open, and the dates you liked go first.\n\n{{company_name}}"],
            ['birthday', 'Birthday greeting', 'whatsapp', null,
                'Happy birthday, {{client_name}}. From all of us at {{company_name}}.'],

            /* Client — finance */
            ['payment_reminder', 'Payment reminder', 'email', 'Payment due — {{reference}}',
                "Dear {{client_name}},\n\nA payment on {{reference}} falls due shortly. The details are on the invoice attached to your charter.\n\n{{company_name}}"],
            ['receipt_issued', 'Receipt issued', 'email', 'Receipt — {{reference}}',
                "Dear {{client_name}},\n\nThank you — your payment has been received and the receipt is attached.\n\n{{company_name}}"],
            ['deposit_refund', 'Deposit refund', 'email', 'Your security deposit',
                "Dear {{client_name}},\n\nYour security deposit has been released and is on its way back to you.\n\n{{company_name}}"],

            /* Internal */
            ['crew_notification', 'Crew dispatch notice', 'whatsapp', null,
                'You are assigned to {{yacht_name}} on {{charter_date}}, reporting at {{charter_time}}. Your dispatch sheet follows.'],
            ['marina_notification', 'Marina notice', 'email', 'Berth request — {{charter_date}}',
                "Good day,\n\nWe expect to be alongside on {{charter_date}}. Please confirm the berth allocation.\n\n{{company_name}}"],
            ['vendor_notification', 'Vendor notice', 'email', 'Order confirmation — {{reference}}',
                "Good day,\n\nPlease confirm the order under {{reference}} for {{charter_date}}.\n\n{{company_name}}"],

            /* Brokerage */
            ['listing_renewal', 'Listing agreement renewal', 'email', 'Your listing agreement',
                "Dear {{client_name}},\n\nThe agreement covering {{yacht_name}} is approaching its end date. We would like to keep going — shall we prepare the renewal?\n\n{{company_name}}"],
            ['post_sale_follow_up', 'Post-sale follow-up', 'email', 'How are you finding her?',
                "Dear {{client_name}},\n\nWe hope {{yacht_name}} is everything you wanted. If you need crew, management or a berth, we can help with all three.\n\n{{company_name}}"],
        ];

        foreach ($templates as [$key, $name, $channel, $subject, $body]) {
            MessageTemplate::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $name,
                    'channel' => $channel,
                    'subject_en' => $subject,
                    'body_en' => $body,
                    'merge_fields' => ['client_name', 'yacht_name', 'charter_date', 'charter_time', 'reference', 'company_name'],
                    'category' => str_contains($key, 'crew') || str_contains($key, 'marina') || str_contains($key, 'vendor')
                        ? 'internal'
                        : 'client',
                    'is_active' => true,
                ],
            );
        }

        $rules = [
            /* key, name, line, trigger type, event, anchor, offset hours, action, template, audience, conditions */
            ['sales.lead_follow_up', 'Follow up a new lead', 'charter', 'event', 'lead.created', null, 24,
                'create_task', null, 'role', null, ['title' => 'Follow up the new lead', 'due_in_hours' => 24]],

            ['charter.booking_confirmation', 'Confirm the booking to the client', 'charter', 'event', 'booking.confirmed', null, 0,
                'send_message', 'booking_confirmation', 'client', null, null],

            ['charter.reminder', 'Remind the client the day before', 'charter', 'schedule', null, 'starts_at', -24,
                'send_message', 'charter_reminder', 'client', [['field' => 'status', 'operator' => 'not_in', 'value' => ['cancelled', 'no_show']]], null],

            ['charter.weather_update', 'Weather note two days out', 'charter', 'schedule', null, 'starts_at', -48,
                'send_message', 'weather_update', 'client', [['field' => 'status', 'operator' => 'not_in', 'value' => ['cancelled', 'no_show']]], null],

            ['charter.crew_notice', 'Tell the crew where to be', 'charter', 'event', 'crew.dispatched', null, 0,
                'send_message', 'crew_notification', 'crew', null, null],

            ['charter.marina_notice', 'Ask the marina for a berth', 'charter', 'schedule', null, 'starts_at', -72,
                'send_message', 'marina_notification', 'vendor', null, null],

            ['finance.payment_reminder', 'Chase a payment before it is late', 'charter', 'schedule', null, 'due_at', -72,
                'send_message', 'payment_reminder', 'client', [['field' => 'status', 'operator' => 'not_equals', 'value' => 'paid']], null],

            ['finance.deposit_refund', 'Tell the client the deposit is back', 'charter', 'event', 'deposit.released', null, 0,
                'send_message', 'deposit_refund', 'client', null, null],

            /* Post-charter — flowchart §15, in the order it lists them. */
            ['post.thank_you', 'Thank the client', 'charter', 'event', 'charter.completed', null, 4,
                'send_message', 'thank_you', 'client', null, null],

            ['post.feedback', 'Ask for feedback', 'charter', 'schedule', null, 'ends_at', 48,
                'send_message', 'feedback_request', 'client', null, null],

            ['post.review', 'Ask for a public review', 'charter', 'schedule', null, 'ends_at', 168,
                'send_message', 'review_request', 'client', null, null],

            ['post.follow_up_30', 'Thirty-day follow-up', 'charter', 'schedule', null, 'ends_at', 720,
                'create_task', null, 'role', null, ['title' => 'Thirty-day follow-up call', 'due_in_hours' => 48]],

            ['post.follow_up_90', 'Ninety-day follow-up', 'charter', 'schedule', null, 'ends_at', 2160,
                'send_message', 'repeat_charter', 'client', null, null],

            ['post.annual', 'Annual reminder', 'charter', 'schedule', null, 'ends_at', 8760,
                'send_message', 'repeat_charter', 'client', null, null],

            /* Brokerage */
            ['brokerage.listing_renewal', 'Warn before the mandate lapses', 'brokerage', 'schedule', null, 'agreement_expires_on', -720,
                'send_message', 'listing_renewal', 'owner', [['field' => 'status', 'operator' => 'equals', 'value' => 'active']], null],

            ['brokerage.post_sale_7', 'Seven-day post-sale check', 'brokerage', 'schedule', null, 'ownership_transferred_at', 168,
                'send_message', 'post_sale_follow_up', 'client', null, null],

            ['brokerage.post_sale_90', 'Ninety-day post-sale check', 'brokerage', 'schedule', null, 'ownership_transferred_at', 2160,
                'create_task', null, 'role', null, ['title' => 'Ninety-day post-sale call', 'due_in_hours' => 72]],

            ['brokerage.post_sale_180', 'Six-month post-sale check', 'brokerage', 'schedule', null, 'ownership_transferred_at', 4320,
                'send_message', 'post_sale_follow_up', 'client', null, null],

            /*
             * Three the first pass left out. The vendor notice is named beside
             * the crew and marina ones in charter §19; brokerage §10 lists four
             * post-sale touches and only three were here; and the birthday
             * greeting had a template written for it but no rule to send it.
             */
            ['charter.vendor_notice', 'Confirm the order with the vendor', 'charter', 'schedule', null, 'starts_at', -72,
                'send_message', 'vendor_notification', 'vendor', null, null, null],

            ['brokerage.post_sale_30', 'Thirty-day post-sale check', 'brokerage', 'schedule', null, 'ownership_transferred_at', 720,
                'send_message', 'post_sale_follow_up', 'client', null, null, null],

            ['client.birthday', 'Wish the client a happy birthday', 'crm', 'schedule', null, 'date_of_birth', 9,
                'send_message', 'birthday', 'client', [['field' => 'status', 'operator' => 'equals', 'value' => 'active']], null, 'annual'],
        ];

        $order = 0;

        foreach ($rules as $rule) {
            [$key, $name, $line, $triggerType, $event, $anchor, $offset, $action, $templateKey, $audience, $conditions, $params] = $rule;
            $recurrence = $rule[12] ?? null;

            WorkflowRule::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $name,
                    'business_line' => $line,
                    'trigger_type' => $triggerType,
                    'trigger_event' => $event,
                    'anchor_field' => $anchor,
                    'recurrence' => $recurrence,
                    'offset_hours' => $offset,
                    'action' => $action,
                    'message_template_id' => $templateKey === null
                        ? null
                        : MessageTemplate::where('key', $templateKey)->value('id'),
                    'action_params' => $params,
                    'audience' => $audience,
                    'conditions' => $conditions,
                    'sort_order' => $order += 10,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info(sprintf(
            'Seeded %d message templates and %d workflow rules.',
            count($templates),
            count($rules),
        ));
    }
}
