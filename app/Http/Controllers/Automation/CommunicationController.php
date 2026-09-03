<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\CommunicationResource;
use App\Models\Communication;

/**
 * Everything the company has ever sent, and what became of it.
 *
 * Read-only by design: the system writes this ledger, and a record of what a
 * client was told is worth nothing if someone can edit it afterwards.
 *
 * @extends ResourceController<Communication>
 */
class CommunicationController extends ResourceController
{
    protected string $model = Communication::class;

    protected string $name = 'communications';

    protected string $pages = 'Automation/Communications';

    protected string $resource = CommunicationResource::class;

    protected ?string $routePrefix = 'engine.communications';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'template'];

    protected array $sortable = ['sent_at', 'channel', 'status'];

    protected string $defaultSort = '-sent_at';

    protected array $filterable = ['channel', 'status', 'direction', 'client_id'];
}
