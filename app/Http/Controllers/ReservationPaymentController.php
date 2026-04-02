<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReservationPaymentController extends Controller
{
    public function checkout(Request $request, int $roomId): HttpResponse|RedirectResponse
    {
        $this->ensureApprovedClient($request);

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
        $totalPriceCents = (int) ($room->price * $nights);

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
                    'currency' => $currency,
                    'expected_amount_cents' => (string) $totalPriceCents,
                ],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Room ' . $room->number . ' Reservation (' . $nights . ' nights)',
                        ],
                        'unit_amount' => $totalPriceCents,
                    ],
                ]],
            ]);
        } catch (ApiErrorException $exception) {
            Log::error('Stripe checkout initiation failed.', [
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('reservations.rooms.show', [
                    'roomId' => $room->id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
                ->with('error', 'Unable to start checkout right now. Please try again.');
        }

        return Inertia::location($session->url);
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        $this->ensureApprovedClient($request);

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
            Log::error('Stripe checkout verification failed.', [
                'session_id' => $validated['session_id'],
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('reservations.create')
                ->with('error', 'Unable to verify payment right now. Please try again.');
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
        $expectedAmountCents = (int) ($metadata['expected_amount_cents'] ?? 0);
        $expectedCurrency = strtolower((string) ($metadata['currency'] ?? ''));
        $amountTotalCents = (int) ($checkoutSession->amount_total ?? 0);
        $paidCurrency = strtolower((string) ($checkoutSession->currency ?? ''));

        if ($clientId !== (int) $request->user()->id || $roomId <= 0 || $checkInDate === '' || $checkOutDate === '') {
            return redirect()
                ->route('reservations.create')
                ->with('error', 'Invalid payment confirmation data.');
        }

        if (
            $amountTotalCents < 1
            || ($expectedAmountCents > 0 && $amountTotalCents !== $expectedAmountCents)
            || ($expectedCurrency !== '' && $paidCurrency !== '' && $expectedCurrency !== $paidCurrency)
        ) {
            Log::warning('Stripe payment amount/currency validation failed.', [
                'session_id' => $validated['session_id'],
                'user_id' => $request->user()->id,
                'expected_amount_cents' => $expectedAmountCents,
                'received_amount_cents' => $amountTotalCents,
                'expected_currency' => $expectedCurrency,
                'received_currency' => $paidCurrency,
            ]);

            return redirect()
                ->route('reservations.create')
                ->with('error', 'Unable to confirm payment details. Please contact support.');
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

        $paidPriceCents = $amountTotalCents;

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
            $paymentIntent = $checkoutSession->payment_intent;
            $paymentIntentId = is_string($paymentIntent)
                ? $paymentIntent
                : ($paymentIntent->id ?? null);

            if ($paymentIntentId) {
                try {
                    Refund::create([
                        'payment_intent' => $paymentIntentId,
                        'reason' => 'requested_by_customer',
                        'metadata' => [
                            'session_id' => $validated['session_id'],
                            'room_id' => (string) $room->id,
                            'user_id' => (string) $request->user()->id,
                        ],
                    ]);

                    Log::warning('Reservation conflict after payment; refund issued.', [
                        'session_id' => $validated['session_id'],
                        'room_id' => $room->id,
                        'user_id' => $request->user()->id,
                    ]);

                    return redirect()
                        ->route('reservations.create', [
                            'check_in_date' => $checkInDate,
                            'check_out_date' => $checkOutDate,
                        ])
                        ->with('error', 'This room became unavailable after payment. Your payment has been refunded.');
                } catch (ApiErrorException $exception) {
                    Log::error('Reservation conflict refund failed.', [
                        'session_id' => $validated['session_id'],
                        'room_id' => $room->id,
                        'user_id' => $request->user()->id,
                        'payment_intent' => $paymentIntentId,
                        'error' => $exception->getMessage(),
                    ]);

                    return redirect()
                        ->route('reservations.create', [
                            'check_in_date' => $checkInDate,
                            'check_out_date' => $checkOutDate,
                        ])
                        ->with('error', 'This room became unavailable after payment. Please contact support for a refund.');
                }
            }

            Log::error('Reservation conflict after payment without payment_intent.', [
                'session_id' => $validated['session_id'],
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
            ]);

            return redirect()
                ->route('reservations.create', [
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                ])
                ->with('error', 'This room became unavailable after payment. Please contact support for a refund.');
        }

        Reservation::create([
            'client_id' => $request->user()->id,
            'room_id' => $room->id,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'accompany_number' => $accompanyNumber,
            'paid_price' => $paidPriceCents,
            'is_active' => true,
        ]);

        return redirect()
            ->route('reservations.index', ['status' => 'success'])
            ->with('success', 'Payment completed and reservation created successfully.');
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

    private function ensureApprovedClient(Request $request): void
    {
        $user = $request->user();

        if (!$user?->hasRole('client')) {
            return;
        }

        abort_unless($user->approved_at !== null, 403, 'Your client account is pending approval.');
    }
}
