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
<<<<<<< HEAD
        $roleNames = $user?->getRoleNames() ?? collect();
=======
>>>>>>> 5b13735357bcb58abbb9d87ab730d608b1cd554c

        return [
            ...parent::share($request),
            'auth' => [
<<<<<<< HEAD
                'role' => $roleNames->first(),
=======
>>>>>>> 5b13735357bcb58abbb9d87ab730d608b1cd554c
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
<<<<<<< HEAD
                    'gender' => $user->gender,
                    'avatar_image' => $user->avatar_image,
                    'approved_at' => $user->approved_at,
                    'last_login_at' => $user->last_login_at,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $roleNames,
=======
                    'roles' => $user->getRoleNames(),
>>>>>>> 5b13735357bcb58abbb9d87ab730d608b1cd554c
                ] : null,
                'role' => $user?->getRoleNames()->first(),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
