<?php

namespace App\Http\Controllers;

use App\Http\Requests\Floor\FloorIndexRequest;
use App\Http\Requests\Floor\FloorStoreRequest;
use App\Http\Requests\Floor\FloorUpdateRequest;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FloorController extends Controller
{
    public function index(FloorIndexRequest $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $validated = $request->validated();

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);
        $sortBy = $validated['sort_by'] ?? null;
        $sortDir = $validated['sort_dir'] ?? 'asc';

        $floorsQuery = Floor::query()
            ->with('manager:id,name')
            ->select(['id', 'name', 'number', 'created_by']);

        if (in_array($sortBy, ['name', 'number'], true)) {
            $floorsQuery->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $sortBy = null;
            $sortDir = 'asc';
            $floorsQuery->orderBy('id');
        }

        if ($search !== '') {
            $floorsQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%");
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
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Floors/Create');
    }

    public function store(FloorStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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

    public function update(FloorUpdateRequest $request, Floor $floor): RedirectResponse
    {
        $this->ensureCanManageFloor($request, $floor);

        $validated = $request->validated();

        $floor->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('floors.index');
    }

    public function destroy(Request $request, Floor $floor): RedirectResponse
    {
        $this->ensureCanManageFloor($request, $floor);

        if (Room::query()->where('floor_id', $floor->id)->exists()) {
            throw ValidationException::withMessages([
                'floor' => 'Cannot delete this floor because rooms are attached to it.',
            ]);
        }

        $floor->delete();

        return redirect()->back();
    }

    private function ensureCanManageFloor(Request $request, Floor $floor): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

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
