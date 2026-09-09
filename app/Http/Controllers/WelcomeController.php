<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $user = auth()->user();

        if ($user?->isLandlord()) {
            return redirect()->route('landlord.dashboard');
        }

        if ($user?->isTenant()) {
            return redirect()->route('tenant.home');
        }

        if ($user) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Welcome');
    }
}
