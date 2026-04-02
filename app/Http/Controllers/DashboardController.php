<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $role = $user?->getRoleNames()->first() ?? 'client';

        return Inertia::render('Dashboard', [
            'role' => $role,
            'overview' => $this->buildOverview($user->id, $role),
        ]);
    }

    private function buildOverview(int $userId, string $role): array
    {
        return match ($role) {
            'admin' => $this->adminOverview(),
            'manager' => $this->managerOverview(),
            'receptionist' => $this->receptionistOverview(),
            default => $this->clientOverview($userId),
        };
    }

    private function adminOverview(): array
    {
        $managerCount = User::role('manager')->count();
        $receptionistCount = User::role('receptionist')->count();
        $clientCount = User::role('client')->count();
        $approvedClientCount = User::role('client')->whereNotNull('approved_at')->count();
        $activeReservationCount = Reservation::query()->active()->count();
        $availableRoomCount = Room::query()
            ->whereDoesntHave('reservations', fn ($query) => $query->active())
            ->count();

        return [
            'eyebrow' => 'Administrator overview',
            'title' => 'Operate the full hotel workspace from one control surface.',
            'description' => 'Track staffing coverage, guest pipeline volume, and live room inventory before moving into the detailed management screens.',
            'stats' => [
                ['label' => 'Managers', 'value' => $managerCount, 'help' => 'Leadership accounts'],
                ['label' => 'Receptionists', 'value' => $receptionistCount, 'help' => 'Front desk staffing'],
                ['label' => 'Clients', 'value' => $clientCount, 'help' => 'Registered guest accounts'],
                ['label' => 'Active Reservations', 'value' => $activeReservationCount, 'help' => 'Current occupied bookings'],
            ],
            'highlights' => [
                ['label' => 'Approved clients', 'value' => $approvedClientCount, 'tone' => 'positive'],
                ['label' => 'Available rooms', 'value' => $availableRoomCount, 'tone' => 'neutral'],
                ['label' => 'Floors configured', 'value' => Floor::query()->count(), 'tone' => 'neutral'],
            ],
            'actions' => [
                ['label' => 'Manage Managers', 'href' => route('managers.index'), 'style' => 'primary'],
                ['label' => 'Review Receptionists', 'href' => route('receptionists.index'), 'style' => 'secondary'],
                ['label' => 'Clients Reservations', 'href' => route('reservations.clients.index'), 'style' => 'secondary'],
            ],
            'panels' => [
                [
                    'title' => 'Staffing snapshot',
                    'body' => 'Use this workspace to supervise hotel staff setup before you expand into floors, rooms, and reservations.',
                    'items' => [
                        "Management layer: {$managerCount} manager account(s)",
                        "Front desk layer: {$receptionistCount} receptionist account(s)",
                        "Guest base: {$clientCount} client account(s)",
                    ],
                ],
                [
                    'title' => 'Operations focus',
                    'body' => 'The highest leverage follow-up is keeping desk staffing and room inventory synchronized with active reservations.',
                    'items' => [
                        "{$activeReservationCount} active reservation(s) currently in progress",
                        "{$availableRoomCount} room(s) still available for new bookings",
                        "{$approvedClientCount} client account(s) already approved for service",
                    ],
                ],
            ],
        ];
    }

    private function managerOverview(): array
    {
        $receptionistCount = User::role('receptionist')->count();
        $clientCount = User::role('client')->count();
        $activeReservationCount = Reservation::query()->active()->count();
        $roomCount = Room::query()->count();
        $availableRoomCount = Room::query()
            ->whereDoesntHave('reservations', fn ($query) => $query->active())
            ->count();

        return [
            'eyebrow' => 'Manager overview',
            'title' => 'Watch staffing, capacity, and booking flow across the property.',
            'description' => 'This overview keeps the property-level numbers visible so you can move directly into receptionist management and operational follow-up.',
            'stats' => [
                ['label' => 'Receptionists', 'value' => $receptionistCount, 'help' => 'Assigned desk accounts'],
                ['label' => 'Clients', 'value' => $clientCount, 'help' => 'Guest records in system'],
                ['label' => 'Rooms', 'value' => $roomCount, 'help' => 'Configured room inventory'],
                ['label' => 'Active Reservations', 'value' => $activeReservationCount, 'help' => 'Live booking load'],
            ],
            'highlights' => [
                ['label' => 'Available rooms', 'value' => $availableRoomCount, 'tone' => 'positive'],
                ['label' => 'Floors configured', 'value' => Floor::query()->count(), 'tone' => 'neutral'],
                ['label' => 'Approved clients', 'value' => User::role('client')->whereNotNull('approved_at')->count(), 'tone' => 'neutral'],
            ],
            'actions' => [
                ['label' => 'Manage Receptionists', 'href' => route('receptionists.index'), 'style' => 'primary'],
                ['label' => 'Clients Reservations', 'href' => route('reservations.clients.index'), 'style' => 'secondary'],
            ],
            'panels' => [
                [
                    'title' => 'Desk readiness',
                    'body' => 'Reception capacity drives guest throughput. Keep the front desk staffed before booking pressure rises.',
                    'items' => [
                        "{$receptionistCount} receptionist account(s) available",
                        "{$activeReservationCount} active stay/stays need ongoing support",
                        "{$availableRoomCount} room(s) available for immediate sale",
                    ],
                ],
                [
                    'title' => 'Property inventory',
                    'body' => 'Room and floor totals indicate whether your core hotel setup is ready for broader management screens.',
                    'items' => [
                        "{$roomCount} room(s) configured",
                        Floor::query()->count() . ' floor(s) currently configured',
                        "{$clientCount} client account(s) in the guest pipeline",
                    ],
                ],
            ],
        ];
    }

    private function receptionistOverview(): array
    {
        $pendingClientCount = User::role('client')->whereNull('approved_at')->count();
        $approvedClientCount = User::role('client')->whereNotNull('approved_at')->count();
        $reservationCount = Reservation::query()->count();
        $activeReservationCount = Reservation::query()->active()->count();

        return [
            'eyebrow' => 'Reception overview',
            'title' => 'Keep the front desk queue moving and guest records clean.',
            'description' => 'Your overview focuses on approvals, guest readiness, and reservation activity so you can react quickly from the desk.',
            'stats' => [
                ['label' => 'Pending Clients', 'value' => $pendingClientCount, 'help' => 'Waiting for approval'],
                ['label' => 'Approved Clients', 'value' => $approvedClientCount, 'help' => 'Ready for service'],
                ['label' => 'Reservations', 'value' => $reservationCount, 'help' => 'Total booking records'],
                ['label' => 'Active Reservations', 'value' => $activeReservationCount, 'help' => 'Current live stays'],
            ],
            'highlights' => [
                ['label' => 'Available rooms', 'value' => Room::query()->whereDoesntHave('reservations', fn ($query) => $query->active())->count(), 'tone' => 'positive'],
                ['label' => 'Guest accounts', 'value' => User::role('client')->count(), 'tone' => 'neutral'],
                ['label' => 'Floors visible', 'value' => Floor::query()->count(), 'tone' => 'neutral'],
            ],
            'actions' => [
                ['label' => 'Clients Reservations', 'href' => route('reservations.clients.index'), 'style' => 'primary'],
                ['label' => 'Open Profile', 'href' => route('profile.edit'), 'style' => 'secondary'],
            ],
            'panels' => [
                [
                    'title' => 'Approval queue',
                    'body' => 'Guest onboarding is the most time-sensitive desk task because it affects every downstream reservation action.',
                    'items' => [
                        "{$pendingClientCount} client account(s) still pending review",
                        "{$approvedClientCount} client account(s) already approved",
                        "{$reservationCount} reservation record(s) currently on file",
                    ],
                ],
                [
                    'title' => 'Shift priorities',
                    'body' => 'Prioritize clients waiting on approval, then handle ongoing reservations and room availability questions.',
                    'items' => [
                        "{$activeReservationCount} active reservation(s) may require follow-up",
                        Room::query()->whereDoesntHave('reservations', fn ($query) => $query->active())->count() . ' room(s) currently available',
                        'Use the reservations workspace as the fastest operational handoff point',
                    ],
                ],
            ],
        ];
    }

    private function clientOverview(int $userId): array
    {
        $myReservationCount = Reservation::query()->where('client_id', $userId)->count();
        $myActiveReservationCount = Reservation::query()->where('client_id', $userId)->active()->count();
        $availableRoomCount = Room::query()
            ->whereDoesntHave('reservations', fn ($query) => $query->active())
            ->count();

        return [
            'eyebrow' => 'Client overview',
            'title' => 'See your booking status and move directly into room reservation.',
            'description' => 'This page keeps your current reservation activity and live room availability visible before you book your next stay.',
            'stats' => [
                ['label' => 'My Reservations', 'value' => $myReservationCount, 'help' => 'All reservations on your account'],
                ['label' => 'Active Stays', 'value' => $myActiveReservationCount, 'help' => 'Currently active reservations'],
                ['label' => 'Available Rooms', 'value' => $availableRoomCount, 'help' => 'Ready for booking now'],
                ['label' => 'Hotel Floors', 'value' => Floor::query()->count(), 'help' => 'Configured property layout'],
            ],
            'highlights' => [
                ['label' => 'Rooms inventory', 'value' => Room::query()->count(), 'tone' => 'neutral'],
                ['label' => 'My latest reservation status', 'value' => $myActiveReservationCount > 0 ? 'Active' : 'No active stay', 'tone' => $myActiveReservationCount > 0 ? 'positive' : 'neutral'],
                ['label' => 'Reservation workspace', 'value' => 'Ready', 'tone' => 'positive'],
            ],
            'actions' => [
                ['label' => 'Make Reservation', 'href' => route('reservations.create'), 'style' => 'primary'],
                ['label' => 'My Reservations', 'href' => route('reservations.index'), 'style' => 'secondary'],
            ],
            'panels' => [
                [
                    'title' => 'Your stay status',
                    'body' => 'Use this overview to decide whether you need a new reservation or want to review your current booking history.',
                    'items' => [
                        "{$myReservationCount} reservation(s) linked to your account",
                        "{$myActiveReservationCount} active reservation(s) right now",
                        "{$availableRoomCount} room(s) available for booking",
                    ],
                ],
                [
                    'title' => 'Next actions',
                    'body' => 'If you do not currently have an active stay, move straight to the reservation page and select from available rooms.',
                    'items' => [
                        'Use Make Reservation to book an available room',
                        'Use My Reservations to review current and past stays',
                        'Update your profile if your contact information changed',
                    ],
                ],
            ],
        ];
    }
}
