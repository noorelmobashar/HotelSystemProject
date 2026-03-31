<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeManagerOrReceptionist($request);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,approved,pending'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $search = trim((string) ($validated['search'] ?? ''));
        $status = (string) ($validated['status'] ?? 'all');

        $clients = User::query()
            ->role('client')
            ->with('approvedBy:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%");
                });
            })
            ->when($status === 'approved', fn ($query) => $query->whereNotNull('approved_at'))
            ->when($status === 'pending', fn ($query) => $query->whereNull('approved_at'))
            ->latest()
            ->paginate($perPage)
            ->through(fn (User $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'gender' => $client->gender,
                'national_id' => $client->national_id,
                'approved_at' => $client->approved_at?->format('Y-m-d H:i'),
                'approved_by_name' => $client->approvedBy?->name,
                'created_at' => $client->created_at?->format('Y-m-d H:i'),
            ])
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'canApprove' => $request->user()?->hasAnyRole(['admin', 'manager', 'receptionist']) ?? false,
        ]);
    }

    public function approve(Request $request, User $client): RedirectResponse
    {
        $this->authorizeManagerOrReceptionist($request);
        $this->ensureClient($client);

        if ($client->approved_at !== null) {
            return back()->with('success', 'Client is already approved.');
        }

        $client->approved_by = $request->user()->id;
        $client->approved_at = now();
        $client->save();
        $client->notify(new ClientApprovedNotification());

        return back()->with('success', 'Client approved successfully.');
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

    private function authorizeManagerOrReceptionist(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'manager', 'receptionist']), 403);
    }

    private function ensureClient(User $user): void
    {
        abort_unless($user->hasRole('client'), 404);
    }
}
