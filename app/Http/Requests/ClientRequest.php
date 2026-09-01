<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The server is the source of truth for validation. The React form mirrors
 * these rules in Zod, and a test asserts the two stay in step (D-014).
 */
class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client
            ? $this->user()->can('update', $client)
            : $this->user()->can('create', Client::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $vip = $this->user()->can('clients.view-vip');

        return [
            'salutation' => ['nullable', 'string', 'max:32'],
            'first_name' => ['required', 'string', 'max:90'],
            'last_name' => ['nullable', 'string', 'max:90'],
            'full_name_ar' => ['nullable', 'string', 'max:190'],

            'client_type' => ['required', 'array', 'min:1'],
            'client_type.*' => ['string', Rule::in(['charter_guest', 'buyer', 'seller', 'owner', 'partner'])],

            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'position' => ['nullable', 'string', 'max:120'],

            'email' => ['nullable', 'email:rfc', 'max:190'],
            'mobile' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'phone_alt' => ['nullable', 'string', 'max:32'],
            'preferred_channel' => ['required', Rule::in(['whatsapp', 'email', 'phone', 'agent'])],

            'nationality' => ['nullable', 'string', 'max:90'],
            'address_line1' => ['nullable', 'string', 'max:190'],
            'address_line2' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:90'],
            'emirate' => ['nullable', 'string', 'max:90'],
            'country' => ['nullable', 'string', 'max:90'],

            'vip_level' => ['required', Rule::in(['none', 'vip', 'uhnw', 'protected'])],
            'status' => ['required', Rule::in(['active', 'dormant', 'pending_approval', 'blacklisted'])],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'source_id' => ['nullable', 'integer', 'exists:lead_sources,id'],
            'notes' => ['nullable', 'string', 'max:5000'],

            // Identity and VIP data are only accepted from a user allowed to
            // see them — otherwise a form post could write fields the sender
            // cannot read back.
            'date_of_birth' => [Rule::excludeIf(! $vip), 'nullable', 'date', 'before:today'],
            'passport_number' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:64'],
            'passport_expiry' => [Rule::excludeIf(! $vip), 'nullable', 'date'],
            'emirates_id' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:64'],
            'trn' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:32'],
            'dietary_preferences' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:2000'],
            'allergies' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:2000'],
            'notes_vip' => [Rule::excludeIf(! $vip), 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_type.required' => 'Choose at least one type — a client can be several at once.',
            'mobile.regex' => 'Use an international format, for example +971 50 123 4567.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => preg_replace('/\s+/', ' ', trim((string) $this->input('mobile')))]);
        }
    }
}
