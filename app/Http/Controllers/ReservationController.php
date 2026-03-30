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
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

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

    public function checkout(Request $request, int $roomId): HttpResponse|RedirectResponse
    {
        $room = Room::query()->whereKey($roomId)->firstOrFail();

        $validated = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'accompany_number' => ['required', 'integer', 'min:0', 'max:' . $room->capacity],
        ], [
            'accompany_number.max' => 'The accompany number cannot exceed the room capacity (' . $room->capacity . ').',
        ]);

        $checkInDate = $validated['check_in_date'];
        $checkOutDate = $validated['check_out_date'];
        $nights = Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate));
        $totalPrice = (int) ($room->price * $nights);

        if ($this->hasRoomConflict($room, $checkInDate, $checkOutDate)) {
            return redirect()
                ->route('reservations.create', [
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
                ->with('error', 'This room is no longer available for the selected dates.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            return redirect()
                ->route('reservations.rooms.show', [
                    'roomId' => $room->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
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
                'cancel_url' => route('reservations.rooms.show', [
                    'roomId' => $room->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ]),
                'client_reference_id' => (string) $request->user()->id,
                'metadata' => [
                    'room_id' => (string) $room->id,
                    'client_id' => (string) $request->user()->id,
                    'accompany_number' => (string) $validated['accompany_number'],
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'nights' => (string) $nights,
                ],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Room ' . $room->number . ' Reservation (' . $nights . ' nights)',
                        ],
                        'unit_amount' => (int) round($totalPrice * 100),
                    ],
                ]],
            ]);
        } catch (ApiErrorException $exception) {
            return redirect()
                ->route('reservations.rooms.show', [
                    'roomId' => $room->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
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
        $checkInDate = (string) ($metadata['check_in_date'] ?? '');
        $checkOutDate = (string) ($metadata['check_out_date'] ?? '');

        if ($clientId !== (int) $request->user()->id || $roomId <= 0 || $checkInDate === '' || $checkOutDate === '') {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Invalid payment confirmation data.');
        }

        try {
            $nights = Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate));
        } catch (\Throwable $exception) {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Invalid reservation dates in payment confirmation.');
        }

        if ($nights < 1) {
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

        $totalPrice = (int) ($room->price * $nights);

        if ($accompanyNumber > $room->capacity) {
            return redirect()
                ->route('reservations.rooms.show', [
                    'roomId' => $room->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
                ->with('error', 'The accompany number cannot exceed room capacity.');
        }

        $existingReservation = Reservation::query()
            ->where('client_id', $request->user()->id)
            ->where('room_id', $room->id)
            ->whereDate('check_in_date', $checkInDate)
            ->whereDate('check_out_date', $checkOutDate)
            ->active()
            ->first();

        if ($existingReservation) {
            return redirect()
                ->route('reservations.index', ['status' => 'already_confirmed'])
                ->with('success', 'Reservation confirmed.');
        }

        if ($this->hasRoomConflict($room, $checkInDate, $checkOutDate)) {
            return redirect()
                ->route('reservations.create', [
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
                ->with('error', 'This room is no longer available for the selected dates.');
        }

        Reservation::create([
            'client_id' => $request->user()->id,
            'room_id' => $room->id,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'accompany_number' => $accompanyNumber,
            'paid_price' => $totalPrice,
            'is_active' => true,
        ]);

        return redirect()
            ->route('reservations.index', ['status' => 'success'])
            ->with('success', 'Payment completed and reservation created successfully.');
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
