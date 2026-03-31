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
        $roleNames = $user?->getRoleNames() ?? collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar_image' => $request->user()->avatar_image,
                    'gender' => $request->user()->gender,
                    'approved_at' => $request->user()->approved_at,
                    'last_login_at' => $request->user()->last_login_at,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'roles' => $request->user()->getRoleNames(),
                ] : null,
                'role' => $request->user()?->getRoleNames()->first(),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
