<?php

declare(strict_types=1);

namespace App\Policies;

class SurveyPolicy extends ResourcePolicy
{
    protected string $prefix = 'surveys';

    protected ?string $ownerColumn = null;
}
