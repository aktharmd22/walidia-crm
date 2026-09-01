<?php

declare(strict_types=1);

namespace App\Domain\Gates\Exceptions;

use App\Domain\Gates\GateResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when a hard gate refuses a transition.
 *
 * It carries the whole result, not just a message, so the screen can name every
 * missing condition and link to the place each one is fixed.
 */
class GateBlockedException extends RuntimeException
{
    public function __construct(public readonly GateResult $result)
    {
        parent::__construct($result->summary());
    }

    public function render(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['gate' => $this->result->toArray()], 422);
        }

        return back()
            ->with('error', $this->result->summary())
            ->with('gate', $this->result->toArray())
            ->withErrors(['gate' => $this->result->summary()]);
    }
}
