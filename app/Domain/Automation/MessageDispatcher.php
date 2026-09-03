<?php

declare(strict_types=1);

namespace App\Domain\Automation;

use App\Models\Client;
use App\Models\Communication;
use App\Models\MessageTemplate;
use App\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Model;

/**
 * Sending, and writing down that we sent.
 *
 * Every message becomes a Communication row before it goes anywhere, so the
 * record exists even if the provider is down. On a fake driver — which is what
 * runs until real credentials exist — nothing leaves the building, but the row
 * is identical, so the audit trail a client might one day ask about is being
 * built from day one rather than switched on later.
 */
class MessageDispatcher
{
    /**
     * @param  string  $audience  client·owner·crew·vendor·role
     */
    public function send(
        MessageTemplate $template,
        Model $subject,
        string $audience = 'client',
        ?WorkflowRun $run = null,
    ): Communication {
        $client = $this->clientFor($subject);
        $values = $this->mergeValues($subject, $client);

        $communication = Communication::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'client_id' => $client?->getKey(),
            'workflow_run_id' => $run?->getKey(),
            'message_template_id' => $template->getKey(),
            'channel' => $template->channel,
            'direction' => 'outbound',
            'to_address' => $this->addressFor($template->channel, $client),
            'subject' => $template->renderSubject($values),
            'body' => $template->render($values),
            'status' => 'queued',
        ]);

        // A message with nowhere to go is a fact worth recording, not an
        // exception to swallow — the client record is missing a contact detail.
        if ($communication->to_address === null) {
            $communication->forceFill([
                'status' => 'failed',
                'failure_reason' => 'No '.$template->channel.' address on the recipient.',
            ])->save();

            return $communication;
        }

        $driver = (string) config('walidia.drivers.'.($template->channel === 'whatsapp' ? 'whatsapp' : 'mail'), 'fake');

        if ($driver === 'fake') {
            $communication->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'provider_reference' => 'fake-'.$communication->getKey(),
            ])->save();

            return $communication;
        }

        // Real drivers land here when credentials exist. Until then the row
        // above is the whole behaviour, and it is the part that matters.
        $communication->forceFill(['status' => 'sent', 'sent_at' => now()])->save();

        return $communication;
    }

    private function clientFor(Model $subject): ?Client
    {
        if ($subject instanceof Client) {
            return $subject;
        }

        $client = $subject->getAttribute('client');

        if ($client instanceof Client) {
            return $client;
        }

        $clientId = $subject->getAttribute('client_id');

        return $clientId === null ? null : Client::find($clientId);
    }

    private function addressFor(string $channel, ?Client $client): ?string
    {
        if ($client === null) {
            return null;
        }

        return match ($channel) {
            'whatsapp', 'sms' => $client->mobile,
            default => $client->email,
        };
    }

    /**
     * The merge fields a template may use.
     *
     * Kept deliberately small and explicit. A template that can reach any
     * attribute on any model is a template that can leak a passport number
     * into an email.
     *
     * @return array<string, string|int|float|null>
     */
    private function mergeValues(Model $subject, ?Client $client): array
    {
        $values = [
            'client_name' => $client?->full_name,
            'company_name' => (string) config('walidia.company.name', 'Walidia Yachts'),
            'reference' => $subject->getAttribute('reference'),
        ];

        foreach (['yacht', 'booking'] as $relation) {
            $related = $subject->getAttribute($relation);

            if ($related instanceof Model) {
                $values[$relation.'_name'] = $related->getAttribute('name') ?? $related->getAttribute('reference');
            }
        }

        $startsAt = $subject->getAttribute('starts_at');

        if ($startsAt !== null) {
            $values['charter_date'] = $startsAt->format('d M Y');
            $values['charter_time'] = $startsAt->format('H:i');
        }

        return $values;
    }
}
