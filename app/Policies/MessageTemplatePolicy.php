<?php

declare(strict_types=1);

namespace App\Policies;

class MessageTemplatePolicy extends ResourcePolicy
{
    protected string $prefix = 'message-templates';

    /** Automation is configuration: it belongs to the company, not a person. */
    protected ?string $ownerColumn = null;
}
