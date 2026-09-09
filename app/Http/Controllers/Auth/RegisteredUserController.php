<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the landlord registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming landlord registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $organizationName = trim((string) ($validated['organization_name'] ?? ''));

        if ($organizationName === '') {
            $organizationName = $validated['name']."'s Properties";
        }

        $user = DB::transaction(function () use ($validated, $organizationName) {
            $organization = Organization::query()->create([
                'name' => $organizationName,
                'slug' => Organization::uniqueSlug($organizationName),
                'email' => $validated['email'],
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ]);

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'current_organization_id' => $organization->id,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            $organization->users()->attach($user->id, [
                'role' => OrganizationRole::Landlord->value,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('landlord.dashboard');
    }
}
