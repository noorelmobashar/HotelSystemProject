<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReceptionistController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeManagement($request);

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
        $search = trim((string) $request->string('search', ''));
        $sortBy = (string) $request->string('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->string('sort_dir', 'desc'));

        $allowedSorts = [
            'name',
            'email',
            'national_id',
            'gender',
            'last_login_at',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $receptionists = User::query()
            ->role('receptionist')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%");

                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                        $nestedQuery
                            ->orWhereDate('created_at', $search)
                            ->orWhereDate('last_login_at', $search);
                    }
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'gender' => $user->gender,
                    'national_id' => $user->national_id,
                    'last_login_at' => $user->last_login_at?->format('Y-m-d H:i'),
                    'created_at' => $user->created_at?->format('Y-m-d H:i'),
                    'avatar_image' => $user->avatar_image,
                ];
            });

        return Inertia::render('Receptionists/Index', [
            'receptionists' => $receptionists,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'national_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $receptionist = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
        ]);

        $receptionist->assignRole('receptionist');

        $receptionist->created_by = $request->user()->id;
        $receptionist->approved_by = $request->user()->id;
        $receptionist->approved_at = now();
        $receptionist->save();

        return redirect()
            ->route('receptionists.index')
            ->with('success', 'Receptionist created successfully.');
    }

    public function update(Request $request, User $receptionist): RedirectResponse
    {
        $this->authorizeManagement($request);
        $this->ensureReceptionist($receptionist);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($receptionist->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $receptionist->name = $validated['name'];
        $receptionist->email = $validated['email'];

        if (!empty($validated['password'])) {
            $receptionist->password = Hash::make($validated['password']);
        }

        $receptionist->save();

        return redirect()
            ->route('receptionists.index')
            ->with('success', 'Receptionist updated successfully.');
    }

    public function destroy(Request $request, User $receptionist)
    {
        $this->authorizeManagement($request);
        $this->ensureReceptionist($receptionist);

        if (!empty($receptionist->avatar_image) && !str_starts_with($receptionist->avatar_image, 'http')) {
            Storage::disk('public')->delete($receptionist->avatar_image);
        }

        $receptionist->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Receptionist deleted successfully.',
            ]);
        }

        return redirect()
            ->route('receptionists.index')
            ->with('success', 'Receptionist deleted successfully.');
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'manager']), 403);
    }

    private function ensureReceptionist(User $user): void
    {
        abort_unless($user->hasRole('receptionist'), 404);
    }
}

