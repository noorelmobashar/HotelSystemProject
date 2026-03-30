<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class ReservationController extends Controller
{
    public function create(): Response
    {
        $rooms = Room::query()
            ->with('floor:id,name,number')
            ->whereDoesntHave('reservations', fn($query) => $query->active())
            ->orderBy('number')
            ->get(['id', 'number', 'capacity', 'price', 'floor_id'])
            ->map(fn(Room $room) => [
                'id' => $room->id,
                'number' => $room->number,
                'capacity' => $room->capacity,
                'price' => $room->price,
                'floor' => $room->floor?->name ?? $room->floor?->number,
            ]);

        return Inertia::render('Reservations/MakeReservation', [
            'rooms' => $rooms,
        ]);
    }

    public function showRoomReservation(int $roomId): Response|RedirectResponse
    {
        $room = Room::query()
            ->with('floor:id,name,number')
            ->whereKey($roomId)
            ->first();

        if (!$room) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Room not found.');
        }

        if ($room->reservations()->active()->exists()) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'This room is already reserved.');
        }

        return Inertia::render('Reservations/RoomReservation', [
            'room' => [
                'id' => $room->id,
                'number' => $room->number,
                'capacity' => $room->capacity,
                'price' => $room->price,
                'floor' => $room->floor?->name ?? $room->floor?->number,
            ],
        ]);
    }

    public function checkout(Request $request, int $roomId): HttpResponse|RedirectResponse
    {
        $room = Room::query()->whereKey($roomId)->firstOrFail();

        $validated = $request->validate([
            'accompany_number' => ['required', 'integer', 'min:0', 'max:' . $room->capacity],
        ], [
            'accompany_number.max' => 'The accompany number cannot exceed the room capacity (' . $room->capacity . ').',
        ]);

        if ($room->reservations()->active()->exists()) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'This room is no longer available for reservation.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            return redirect()
                ->route('reservations.rooms.show', ['roomId' => $room->id])
                ->with('error', 'Stripe is not configured. Please set STRIPE_SECRET.');
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $currency = strtolower((string) config('services.stripe.currency', 'usd'));
            $paymentMethodTypes = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) config('services.stripe.payment_method_types', 'card')),
            )));

            if (empty($paymentMethodTypes)) {
                $paymentMethodTypes = ['card'];
            }

            $session = Session::create([
                'mode' => 'payment',
                'ui_mode' => 'hosted_page',
                'payment_method_types' => $paymentMethodTypes,
                'success_url' => route('reservations.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('reservations.rooms.show', ['roomId' => $room->id]),
                'client_reference_id' => (string) $request->user()->id,
                'metadata' => [
                    'room_id' => (string) $room->id,
                    'client_id' => (string) $request->user()->id,
                    'accompany_number' => (string) $validated['accompany_number'],
                ],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Room ' . $room->number . ' Reservation',
                        ],
                        'unit_amount' => (int) round($room->price * 100),
                    ],
                ]],
            ]);
        } catch (ApiErrorException $exception) {
            return redirect()
                ->route('reservations.rooms.show', ['roomId' => $room->id])
                ->with('error', 'Unable to initiate Stripe checkout. ' . $exception->getMessage());
        }

        return Inertia::location($session->url);
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Stripe is not configured. Please set STRIPE_SECRET.');
        }

        try {
            Stripe::setApiKey($stripeSecret);
            $checkoutSession = Session::retrieve($validated['session_id']);
        } catch (ApiErrorException $exception) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Unable to verify Stripe payment. ' . $exception->getMessage());
        }

        if ($checkoutSession->payment_status !== 'paid') {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Payment was not completed successfully.');
        }

        // Stripe metadata comes as StripeObject; casting directly to array can drop expected keys.
        $metadata = json_decode(json_encode($checkoutSession->metadata ?? []), true) ?: [];
        $roomId = (int) ($metadata['room_id'] ?? 0);
        $metadataClientId = (int) ($metadata['client_id'] ?? 0);
        $clientReferenceId = (int) ($checkoutSession->client_reference_id ?? 0);
        $clientId = $clientReferenceId > 0 ? $clientReferenceId : $metadataClientId;
        $accompanyNumber = (int) ($metadata['accompany_number'] ?? 0);

        if ($clientId !== (int) $request->user()->id || $roomId <= 0) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Invalid payment confirmation data.');
        }

        $room = Room::query()->whereKey($roomId)->first();
        if (!$room) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'The selected room no longer exists.');
        }

        if ($accompanyNumber > $room->capacity) {
            return redirect()
                ->route('reservations.rooms.show', ['roomId' => $room->id])
                ->with('error', 'The accompany number cannot exceed room capacity.');
        }

        $existingReservation = Reservation::query()
            ->where('client_id', $request->user()->id)
            ->where('room_id', $room->id)
            ->active()
            ->first();

        if ($existingReservation) {
            return redirect()
                ->route('reservations.index', ['status' => 'already_confirmed'])
                ->with('success', 'Reservation confirmed.');
        }

        if ($room->reservations()->active()->exists()) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'This room is no longer available.');
        }

        Reservation::create([
            'client_id' => $request->user()->id,
            'room_id' => $room->id,
            'accompany_number' => $accompanyNumber,
            'paid_price' => $room->price,
            'is_active' => true,
        ]);

        return redirect()
            ->route('reservations.index', ['status' => 'success'])
            ->with('success', 'Payment completed and reservation created successfully.');
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
                'accompany_number' => $reservation->accompany_number,
                'paid_price' => $reservation->paid_price,
                'is_active' => $reservation->is_active,
                'created_at' => $reservation->created_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Reservations/MyReservations', [
            'reservations' => $reservations,
        ]);
    }
}
