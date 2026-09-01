<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CostSheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Sales prices the quote; Finance owns what was invoiced and what actually
 * happened. Nobody writes a closed sheet.
 */
class CostSheetPolicy extends ResourcePolicy
{
    protected string $prefix = 'cost-sheets';

    protected ?string $ownerColumn = null;

    public function update(User $user, Model $model): bool
    {
        return $model instanceof CostSheet
            && ! $model->isClosed()
            && $model->writablePhasesFor($user) !== [];
    }

    public function writePhase(User $user, CostSheet $sheet, string $phase): bool
    {
        return in_array($phase, $sheet->writablePhasesFor($user), true);
    }

    public function close(User $user, CostSheet $sheet): bool
    {
        return $user->can('cost-sheets.close') && ! $sheet->isClosed();
    }
}
