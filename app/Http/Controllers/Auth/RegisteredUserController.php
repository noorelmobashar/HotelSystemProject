<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'countries' => $this->countries(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $countries = $this->countries();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'country' => ['required', 'string', Rule::in($countries)],
            'avatar_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $avatarPath = $request->hasFile('avatar_image')
            ? $request->file('avatar_image')->store('avatars', 'public')
            : '/images/default-avatar.svg';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'avatar_image' => $avatarPath,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('client');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * @return array<int, string>
     */
    private function countries(): array
    {
        return Cache::rememberForever('registration_countries_dropdown', function () {
            return [
                'Egypt',
                'Saudi Arabia',
                'United Arab Emirates',
                'Jordan',
                'Kuwait',
                'Qatar',
                'Bahrain',
                'Oman',
                'Lebanon',
                'Iraq',
                'Morocco',
                'Algeria',
                'Tunisia',
                'Libya',
                'Sudan',
                'Yemen',
                'United States',
                'United Kingdom',
                'Canada',
                'Germany',
                'France',
                'Italy',
                'Spain',
                'Turkey',
                'India',
                'China',
                'Japan',
                'Australia',
            ];
        });
    }
}
