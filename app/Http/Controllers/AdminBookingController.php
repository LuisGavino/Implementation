<?php

namespace App\Http\Controllers;

use App\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administrator booking management (all customers' bookings).
 * Routes are wrapped in 'role:admin'; BookingPolicy is a second layer.
 */
class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Booking::class);

        $status = $request->query('status');

        $bookings = Booking::with(['user', 'destination'])
            ->when(in_array($status, Booking::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
        ]);

        $booking->update(['status' => $validated['status']]);

        session()->flash('success', 'Booking status updated');

        return redirect()->back();
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $booking->delete();

        session()->flash('success', 'Booking deleted');

        return redirect()->back();
    }
}
