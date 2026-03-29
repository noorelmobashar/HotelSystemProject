<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(): Response
    {
        $rooms = Room::query()
            ->with('floor:id,name,number')
            ->whereDoesntHave('reservations', fn($query) => $query->active())
            ->orderBy('number')
            ->get(['id', 'number', 'capacity', 'price', 'floor_id']);

        return Inertia::render('Reservations/MakeReservation', [
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);

        $room = Room::query()
            ->whereKey($validated['room_id'])
            ->whereDoesntHave('reservations', fn($query) => $query->active())
            ->first();

        if (!$room) {
            return redirect()
                ->route('reservations.create')
                ->withErrors(['room' => 'This room is no longer available for reservation.']);
        }

        Reservation::create([
            'client_id' => $request->user()->id,
            'room_id' => $room->id,
            'accompany_number' => 0,
            'paid_price' => $room->price,
            'is_active' => true,
        ]);

        return redirect()
            ->route('reservations.create')
            ->with('success', 'Reservation created successfully.');
    }

    public function index(Request $request): Response
    {
        $reservations = Reservation::query()
            ->with('room:id,number,capacity,price')
            ->where('client_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn(Reservation $reservation) => [
                'id' => $reservation->id,
                'room_number' => $reservation->room?->number,
                'capacity' => $reservation->room?->capacity,
                'paid_price' => $reservation->paid_price,
                'is_active' => $reservation->is_active,
                'created_at' => $reservation->created_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Reservations/MyReservations', [
            'reservations' => $reservations,
        ]);
    }
}
