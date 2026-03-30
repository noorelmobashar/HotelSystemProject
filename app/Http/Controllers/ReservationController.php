<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(Request $request): Response
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $rooms = Room::query()
            ->with('floor:id,name,number')
            ->whereDoesntHave('reservations', function (Builder $query) {
                $query
                    ->where('is_active', true)
                    ->where(function (Builder $builder) {
                        $builder
                            ->whereNull('check_out_date')
                            ->orWhereDate('check_out_date', '>=', now()->toDateString());
                    });
            })
            ->orderBy('number')
            ->paginate($perPage, ['id', 'number', 'capacity', 'price', 'floor_id'])
            ->through(fn(Room $room) => [
                'id' => $room->id,
                'number' => $room->number,
                'capacity' => $room->capacity,
                'price' => $room->price,
                'floor' => $room->floor?->name ?? $room->floor?->number,
            ])
            ->withQueryString();

        return Inertia::render('Reservations/MakeReservation', [
            'rooms' => $rooms,
            'filters' => [
                'per_page' => $perPage,
            ],
        ]);
    }

    public function showRoomReservation(Request $request, int $roomId): Response|RedirectResponse
    {
        $validated = $request->validate([
            'check_in_date' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
        ]);

        $checkInDate = $validated['check_in_date'] ?? null;
        $checkOutDate = $validated['check_out_date'] ?? null;
        $nights = $checkInDate && $checkOutDate
            ? Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate))
            : null;

        $room = Room::query()
            ->with('floor:id,name,number')
            ->whereKey($roomId)
            ->first();

        if (!$room) {
            return redirect()
                ->route('reservations.create', $validated)
                ->with('error', 'Room not found.');
        }

        if ($checkInDate && $checkOutDate && $this->hasRoomConflict($room, $checkInDate, $checkOutDate)) {
            return redirect()
                ->route('reservations.create', $validated)
                ->with('error', 'This room is not available for the selected period.');
        }

        return Inertia::render('Reservations/RoomReservation', [
            'room' => [
                'id' => $room->id,
                'number' => $room->number,
                'capacity' => $room->capacity,
                'price' => $room->price,
                'floor' => $room->floor?->name ?? $room->floor?->number,
                'check_in_date' => $checkInDate ?? '',
                'check_out_date' => $checkOutDate ?? '',
                'nights' => $nights,
                'total_price' => $nights ? $room->price * $nights : null,
            ],
        ]);
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $reservations = Reservation::query()
            ->with('room:id,number,capacity,price')
            ->where('client_id', $request->user()->id)
            ->latest()
            ->paginate($perPage)
            ->through(fn(Reservation $reservation) => [
                'id' => $reservation->id,
                'room_number' => $reservation->room?->number,
                'check_in_date' => $reservation->check_in_date?->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date?->format('Y-m-d'),
                'nights' => $reservation->check_in_date && $reservation->check_out_date
                    ? $reservation->check_in_date->diffInDays($reservation->check_out_date)
                    : null,
                'capacity' => $reservation->room?->capacity,
                'accompany_number' => $reservation->accompany_number,
                'paid_price' => $reservation->paid_price,
                'is_active' => $reservation->is_active,
                'created_at' => $reservation->created_at?->format('Y-m-d H:i'),
            ])
            ->withQueryString();

        return Inertia::render('Reservations/MyReservations', [
            'reservations' => $reservations,
            'filters' => [
                'per_page' => $perPage,
            ],
        ]);
    }

    private function hasRoomConflict(Room $room, string $checkInDate, string $checkOutDate): bool
    {
        $query = $room->reservations();
        $this->applyOverlappingActiveFilter($query, $checkInDate, $checkOutDate);

        return $query->exists();
    }

    private function applyOverlappingActiveFilter(Builder|HasMany $query, string $checkInDate, string $checkOutDate): void
    {
        $query
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($checkInDate, $checkOutDate) {
                $builder
                    ->where(function (Builder $overlap) use ($checkInDate, $checkOutDate) {
                        $overlap
                            ->whereDate('check_in_date', '<', $checkOutDate)
                            ->whereDate('check_out_date', '>', $checkInDate);
                    })
                    ->orWhereNull('check_in_date')
                    ->orWhereNull('check_out_date');
            });
    }
}
