<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role' => $user?->organizationRole()?->value,
            ],
            'organization' => function () use ($user) {
                if (! $user?->current_organization_id) {
                    return null;
                }

                $organization = $user->relationLoaded('currentOrganization')
                    ? $user->currentOrganization
                    : $user->currentOrganization()->first(['id', 'name']);

                if (! $organization) {
                    return null;
                }

                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                ];
            },
            'notifications' => function () use ($user) {
                if (! $user) {
                    return [
                        'items' => [],
                        'unread_count' => 0,
                    ];
                }

                $unread = $user->unreadNotifications()->latest()->limit(10)->get();

                return [
                    'items' => $unread->map(function ($notification) {
                        $data = $notification->data;

                        return [
                            'id' => $notification->id,
                            'type' => $data['type'] ?? class_basename($notification->type),
                            'title' => $data['title'] ?? 'Notification',
                            'message' => $data['message'] ?? '',
                            'read_at' => $notification->read_at?->toIso8601String(),
                            'created_at' => $notification->created_at?->toIso8601String(),
                            'data' => $data,
                        ];
                    })->values()->all(),
                    'unread_count' => $user->unreadNotifications()->count(),
                ];
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
