<?php

namespace App\Policies;

use App\Booking;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * List ALL bookings (admin booking management). Customers list only their
     * own bookings through $user->bookings(), never through this ability.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * A booking can be viewed by its owner or by an administrator.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (int) $booking->user_id === (int) $user->id;
    }

    /**
     * Any authenticated customer/admin may create a booking for themselves.
     * (The owner is always set server-side from the logged-in user.)
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Changing the status of a booking (confirm, complete, ...) is admin-only.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }

    /**
     * A customer may cancel only their OWN booking, and only while it is pending.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return (int) $booking->user_id === (int) $user->id && $booking->isPending();
    }

    /**
     * Deleting booking records is admin-only.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }
}
