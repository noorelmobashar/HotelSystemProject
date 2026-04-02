<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeClientIndex($request);

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
        $search = trim((string) $request->string('search', ''));
        $sortBy = (string) $request->string('sort_by', 'approval_status');
        $sortDir = strtolower((string) $request->string('sort_dir', 'desc'));

        $allowedSorts = [
            'name',
            'email',
            'national_id',
            'gender',
            'approved_at',
            'created_at',
            'approval_status',
        ];

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'approval_status';
        }

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $isReceptionist = $request->user()?->hasRole('receptionist') ?? false;
        $searchLower = strtolower($search);

        $clients = User::query()
            ->role('client')
            ->with('approvedBy:id,name')
            ->when($isReceptionist, fn($query) => $query->whereNull('approved_at'))
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
                            ->orWhereDate('approved_at', $search);
                    }
                });
            })
            ->when(in_array($searchLower, ['pending', 'unapproved'], true), function ($query) {
                $query->whereNull('approved_at');
            })
            ->when($searchLower === 'approved', function ($query) {
                $query->whereNotNull('approved_at');
            });

        if (!$isReceptionist) {
            // Keep pending clients first for admin/manager, then sort within each bucket.
            $clients->orderByRaw('approved_at IS NULL DESC');
        }

        if ($sortBy === 'approval_status') {
            $clients->orderBy('created_at', $sortDir);
        } else {
            $clients->orderBy($sortBy, $sortDir);
        }

        $clients = $clients
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'gender' => $user->gender,
                    'national_id' => $user->national_id,
                    'approved_at' => $user->approved_at?->format('Y-m-d H:i'),
                    'approved_by' => $user->approvedBy?->name,
                    'created_at' => $user->created_at?->format('Y-m-d H:i'),
                    'status' => $user->approved_at ? 'approved' : 'pending',
                    'avatar_image' => $user->avatar_image,
                ];
            });

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
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
        $this->authorizeManageClients($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'national_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'],
            'national_id' => $validated['national_id'] ?? null,
            'created_by' => $request->user()->id,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $client->assignRole('client');

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function update(Request $request, User $client): RedirectResponse
    {
        $this->authorizeManageClients($request);
        $this->ensureClient($client);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($client->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $client->name = $validated['name'];
        $client->email = $validated['email'];

        if (!empty($validated['password'])) {
            $client->password = Hash::make($validated['password']);
        }

        $client->save();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Request $request, User $client): JsonResponse|RedirectResponse
    {
        $this->authorizeManageClients($request);
        $this->ensureClient($client);

        if (!empty($client->avatar_image) && !str_starts_with($client->avatar_image, 'http') && !str_starts_with($client->avatar_image, '/')) {
            Storage::disk('public')->delete($client->avatar_image);
        }

        $client->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully.',
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function approve(Request $request, User $client): JsonResponse|RedirectResponse
    {
        $this->authorizeApproveClients($request);
        $this->ensureClient($client);

        if (is_null($client->approved_at)) {
            $client->approved_by = $request->user()->id;
            $client->approved_at = now();
            $client->save();
            $client->notify(new ClientApprovedNotification());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Client approved successfully.',
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client approved successfully.');
    }

    public function myApprovedClients(Request $request): Response
    {
        abort_unless($request->user()?->hasRole('receptionist'), 403);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $search = trim((string) ($validated['search'] ?? ''));
        $receptionistId = $request->user()->id;

        $clients = User::query()
            ->role('client')
            ->whereNotNull('approved_at')
            ->where('approved_by', $receptionistId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%");
                });
            })
            ->latest('approved_at')
            ->paginate($perPage)
            ->through(fn (User $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'gender' => $client->gender,
                'national_id' => $client->national_id,
                'approved_at' => $client->approved_at?->format('Y-m-d H:i'),
                'created_at' => $client->created_at?->format('Y-m-d H:i'),
            ])
            ->withQueryString();

        return Inertia::render('Clients/MyApprovedClients', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    private function authorizeClientIndex(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'manager', 'receptionist']), 403);
    }

    private function authorizeApproveClients(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'manager', 'receptionist']), 403);
    }

    private function authorizeManageClients(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'manager']), 403);
    }
    private function ensureClient(User $user): void
    {
        abort_unless($user->hasRole('client'), 404);
    }
}
