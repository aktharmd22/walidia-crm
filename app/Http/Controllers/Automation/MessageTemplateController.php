<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\MessageTemplateResource;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What the system says, in both languages, with the merge fields it expects.
 *
 * @extends ResourceController<MessageTemplate>
 */
class MessageTemplateController extends ResourceController
{
    protected string $model = MessageTemplate::class;

    protected string $name = 'message-templates';

    protected string $pages = 'Automation/MessageTemplates';

    protected string $resource = MessageTemplateResource::class;

    protected ?string $routePrefix = 'engine.message-templates';

    protected array $indexWith = [];

    protected array $showWith = [];

    protected array $sortable = ['name', 'channel', 'category'];

    protected string $defaultSort = 'name';

    protected array $filterable = ['channel', 'category', 'is_active'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MessageTemplate::class);

        $record = MessageTemplate::create($this->validated($request));

        return redirect()->route('engine.message-templates.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate): RedirectResponse
    {
        $this->authorize('update', $messageTemplate);

        $messageTemplate->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /**
     * Preview with sample values, so a broken merge field is visible before a
     * client sees a sentence with a hole in it.
     */
    public function preview(Request $request, MessageTemplate $messageTemplate): RedirectResponse
    {
        $this->authorize('view', $messageTemplate);

        $sample = [
            'client_name' => 'HH Sheikh Ahmed',
            'yacht_name' => 'Lady Walidia',
            'charter_date' => now()->addWeek()->format('d M Y'),
            'charter_time' => '14:00',
            'reference' => 'BK-2026-0042',
            'company_name' => (string) config('walidia.company.name', 'Walidia Yachts'),
        ];

        return back()->with('preview', [
            'subject' => $messageTemplate->renderSubject($sample),
            'body' => $messageTemplate->render($sample),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:64', Rule::unique('message_templates', 'key')->ignore($request->route('messageTemplate'))],
            'name' => ['required', 'string', 'max:190'],
            'channel' => ['required', Rule::in(['email', 'whatsapp', 'sms', 'in_app'])],
            'category' => ['required', Rule::in(['client', 'crew', 'vendor', 'internal'])],
            'subject_en' => ['nullable', 'string', 'max:190'],
            'body_en' => ['required', 'string', 'max:8000'],
            'subject_ar' => ['nullable', 'string', 'max:190'],
            'body_ar' => ['nullable', 'string', 'max:8000'],
            'is_active' => ['boolean'],
        ]);
    }
}
