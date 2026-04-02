<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

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

        $managers = User::query()
            ->role('manager')
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

        return Inertia::render('Managers/Index', [
            'managers' => $managers,
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
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'national_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $manager = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
        ]);

        $manager->assignRole('manager');

        $manager->created_by = $request->user()->id;
        $manager->approved_by = $request->user()->id;
        $manager->approved_at = now();
        $manager->save();

        return redirect()
            ->route('managers.index')
            ->with('success', 'Manager created successfully.');
    }

    public function update(Request $request, User $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureManager($manager);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($manager->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $manager->name = $validated['name'];
        $manager->email = $validated['email'];

        if (!empty($validated['password'])) {
            $manager->password = Hash::make($validated['password']);
        }

        $manager->save();

        return redirect()
            ->route('managers.index')
            ->with('success', 'Manager updated successfully.');
    }

    public function destroy(Request $request, User $manager): JsonResponse|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureManager($manager);

        if (!empty($manager->avatar_image) && !str_starts_with($manager->avatar_image, 'http') && !str_starts_with($manager->avatar_image, '/')) {
            Storage::disk('public')->delete($manager->avatar_image);
        }

        $manager->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Manager deleted successfully.',
            ]);
        }

        return redirect()
            ->route('managers.index')
            ->with('success', 'Manager deleted successfully.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

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

        $managers = User::query()
            ->role('manager')
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
            ->withQueryString();

        return response()->json([
            'data' => collect($managers->items())->map(fn (User $user) => $this->transformManager($user))->values(),
            'meta' => [
                'current_page' => $managers->currentPage(),
                'last_page' => $managers->lastPage(),
                'per_page' => $managers->perPage(),
                'total' => $managers->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function apiShow(Request $request, User $manager): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureManager($manager);

        return response()->json([
            'data' => $this->transformManager($manager),
        ]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'national_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $manager = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
        ]);

        $manager->assignRole('manager');

        $manager->created_by = $request->user()->id;
        $manager->approved_by = $request->user()->id;
        $manager->approved_at = now();
        $manager->save();

        return response()->json([
            'message' => 'Manager created successfully.',
            'data' => $this->transformManager($manager->fresh()),
        ], 201);
    }

    public function apiUpdate(Request $request, User $manager): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureManager($manager);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($manager->id)],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'national_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')->ignore($manager->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $manager->name = $validated['name'];
        $manager->email = $validated['email'];
        $manager->gender = $validated['gender'] ?? $manager->gender;
        $manager->national_id = array_key_exists('national_id', $validated) ? $validated['national_id'] : $manager->national_id;

        if (!empty($validated['password'])) {
            $manager->password = Hash::make($validated['password']);
        }

        $manager->save();

        return response()->json([
            'message' => 'Manager updated successfully.',
            'data' => $this->transformManager($manager->fresh()),
        ]);
    }

    public function apiDestroy(Request $request, User $manager): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureManager($manager);

        if (!empty($manager->avatar_image) && !str_starts_with($manager->avatar_image, 'http') && !str_starts_with($manager->avatar_image, '/')) {
            Storage::disk('public')->delete($manager->avatar_image);
        }

        $manager->delete();

        return response()->json([
            'message' => 'Manager deleted successfully.',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasRole('manager'), 404);
    }

    private function transformManager(User $user): array
    {
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
    }
}
