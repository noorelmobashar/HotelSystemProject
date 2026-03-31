<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\RoomIndexRequest;
use App\Http\Requests\Room\RoomStoreRequest;
use App\Http\Requests\Room\RoomUpdateRequest;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(RoomIndexRequest $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $canCreate = $user->hasRole('admin') || $user->hasRole('manager');

        $validated = $request->validated();

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);
        $sortBy = $validated['sort_by'] ?? null;
        $sortDir = $validated['sort_dir'] ?? 'asc';
        $allowedSortColumns = $isAdmin
            ? ['number', 'capacity', 'price', 'floor_name', 'manager_name']
            : ['number', 'capacity', 'price', 'floor_name'];

        $roomsQuery = Room::query()
            ->with(['floor:id,name,number', 'manager:id,name'])
            ->select(['id', 'number', 'capacity', 'price', 'floor_id', 'created_by']);

        if (in_array($sortBy, $allowedSortColumns, true)) {
            if ($sortBy === 'floor_name') {
                $roomsQuery
                    ->orderBy(
                        Floor::query()
                            ->select('name')
                            ->whereColumn('floors.id', 'rooms.floor_id')
                            ->limit(1),
                        $sortDir
                    )
                    ->orderBy('id');
            } elseif ($sortBy === 'manager_name') {
                $roomsQuery
                    ->orderBy(
                        User::query()
                            ->select('name')
                            ->whereColumn('users.id', 'rooms.created_by')
                            ->limit(1),
                        $sortDir
                    )
                    ->orderBy('id');
            } else {
                $roomsQuery->orderBy($sortBy, $sortDir)->orderBy('id');
            }
        } else {
            $sortBy = null;
            $sortDir = 'asc';
            $roomsQuery->orderBy('id');
        }

        if ($search !== '') {
            $roomsQuery->where(function ($query) use ($search, $isAdmin) {
                $query
                    ->where('number', 'like', "%{$search}%")
                    ->orWhere('capacity', 'like', "%{$search}%")
                    ->orWhereHas('floor', function ($floorQuery) use ($search) {
                        $floorQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%");
                    });

                if ($isAdmin) {
                    $query->orWhereHas('manager', function ($managerQuery) use ($search) {
                        $managerQuery->where('name', 'like', "%{$search}%");
                    });
                }
            });
        }

        $rooms = $roomsQuery
            ->paginate($perPage)
            ->through(function (Room $room) use ($user, $isAdmin) {
                return [
                    'id' => $room->id,
                    'number' => $room->number,
                    'capacity' => $room->capacity,
                    'price' => $room->price / 100,
                    'floor_name' => $room->floor?->name,
                    'floor_number' => $room->floor?->number,
                    'manager_name' => $room->manager?->name,
                    'can_manage' => $isAdmin
                        || ($user->hasRole('manager')
                            && (int) $room->created_by === (int) $user->id),
                ];
            })
            ->withQueryString();

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
            'isAdmin' => $isAdmin,
            'canCreate' => $canCreate,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureCanCreateRoom($request);

        return Inertia::render('Rooms/Create', [
            'floors' => $this->floorOptions(),
        ]);
    }

    public function store(RoomStoreRequest $request): RedirectResponse
    {
        $this->ensureCanCreateRoom($request);

        $validated = $request->validated();

        Room::create([
            'number' => $validated['number'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'] * 100,
            'floor_id' => $validated['floor_id'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('rooms.index');
    }

    public function edit(Request $request, Room $room): Response
    {
        $this->ensureCanManageRoom($request, $room);

        return Inertia::render('Rooms/Edit', [
            'room' => [
                'id' => $room->id,
                'number' => $room->number,
                'capacity' => $room->capacity,
                'price' => (int) ($room->price / 100),
                'floor_id' => $room->floor_id,
            ],
            'floors' => $this->floorOptions(),
        ]);
    }

    public function update(RoomUpdateRequest $request, Room $room): RedirectResponse
    {
        $this->ensureCanManageRoom($request, $room);

        $validated = $request->validated();

        $room->update([
            'number' => $validated['number'],
            'capacity' => $validated['capacity'],
            'price' => $validated['price'] * 100,
            'floor_id' => $validated['floor_id'],
        ]);

        return redirect()->route('rooms.index');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $this->ensureCanManageRoom($request, $room);

        if ($room->reservations()->active()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'Cannot delete this room because it currently has an active reservation.',
            ]);
        }

        $room->delete();

        return redirect()->back();
    }

    private function ensureCanCreateRoom(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return;
        }

        abort(403);
    }

    private function ensureCanManageRoom(Request $request, Room $room): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('manager') && (int) $room->created_by === (int) $user->id) {
            return;
        }

        abort(403);
    }

    /**
     * @return array<int, array{id: int, name: string, number: string}>
     */
    private function floorOptions(): array
    {
        return Floor::query()
            ->orderBy('name')
            ->orderBy('number')
            ->get(['id', 'name', 'number'])
            ->map(fn (Floor $floor) => [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
            ])
            ->all();
    }
}
