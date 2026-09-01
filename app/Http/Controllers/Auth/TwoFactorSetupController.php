<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Auth/TwoFactorSetup', [
            'enabled' => $user->two_factor_secret !== null,
            'confirmed' => $user->hasTwoFactorConfirmed(),
        ]);
    }
}
