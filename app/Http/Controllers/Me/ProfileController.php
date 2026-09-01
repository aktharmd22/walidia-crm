<?php

declare(strict_types=1);

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Me/Profile', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'job_title' => $user->job_title,
                'locale' => $user->locale,
                'chrome' => $user->chrome,
                'accent' => $user->accent,
                'avatar_url' => $user->avatarUrl(),
            ],
            'security' => [
                'two_factor_confirmed' => $user->hasTwoFactorConfirmed(),
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'locale' => ['required', Rule::in(config('walidia.locales'))],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function security(Request $request): Response
    {
        return Inertia::render('Me/Security', [
            'two_factor_confirmed' => $request->user()->hasTwoFactorConfirmed(),
        ]);
    }
}
