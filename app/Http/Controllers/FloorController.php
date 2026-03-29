<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FloorController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureAdminOrManager($request);

        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);

        $floorsQuery = Floor::query()
            ->with('manager:id,name')
            ->select(['id', 'name', 'number', 'created_by'])
            ->orderBy('id');

        if ($search !== '') {
            $floorsQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%");

                $query->orWhereHas('manager', function ($managerQuery) use ($search) {
                    $managerQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $floors = $floorsQuery
            ->paginate($perPage)
            ->through(function (Floor $floor) use ($isAdmin, $user) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'number' => $floor->number,
                    'manager_name' => $floor->manager?->name,
                    'can_manage' => $isAdmin || (int) $floor->created_by === (int) $user->id,
                ];
            })
            ->withQueryString();

        return Inertia::render('Floors/Index', [
            'floors' => $floors,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdminOrManager($request);

        return Inertia::render('Floors/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrManager($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $generatedNumber = $this->generateFloorNumber();

        Floor::create([
            'name' => $validated['name'],
            'number' => $generatedNumber,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('floors.index');
    }

    public function edit(Request $request, Floor $floor): Response
    {
        $this->ensureCanManageFloor($request, $floor);

        return Inertia::render('Floors/Edit', [
            'floor' => $floor->only(['id', 'name', 'number']),
        ]);
    }

    public function update(Request $request, Floor $floor): RedirectResponse
    {
        $this->ensureCanManageFloor($request, $floor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $floor->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('floors.index');
    }

    public function destroy(Request $request, Floor $floor): RedirectResponse
    {
        $this->ensureCanManageFloor($request, $floor);

        $floor->delete();

        return redirect()->route('floors.index');
    }

    private function ensureAdminOrManager(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }
    }

    private function ensureCanManageFloor(Request $request, Floor $floor): void
    {
        $this->ensureAdminOrManager($request);

        $user = $request->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('manager') && (int) $floor->created_by === (int) $user->id) {
            return;
        }

        abort(403);
    }

    private function generateFloorNumber(): string
    {
        $maxNumber = Floor::query()
            ->pluck('number')
            ->map(fn ($number) => (int) $number)
            ->filter(fn ($number) => $number > 0)
            ->max() ?? 0;

        $nextNumber = max(1000, $maxNumber + 1);

        do {
            $candidate = str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Floor::query()->where('number', $candidate)->exists());

        return $candidate;
    }
}
