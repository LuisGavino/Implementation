<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Destinations;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Customer-facing bookings.
 * Every query is scoped to the logged-in user, and every single-record action is
 * checked by BookingPolicy, so IDs typed into the URL can never expose or modify
 * another customer's booking.
 */
class BookingController extends Controller
{
    public function index()
    {
        // Scoped through the relationship: only the current user's bookings.
        $bookings = auth()->user()
            ->bookings()
            ->with('destination')
            ->latest()
            ->paginate(10);

        $destinations = Destinations::orderBy('title')->get(['id', 'title', 'pricing']);

        return view('bookings.index', compact('bookings', 'destinations'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load('destination', 'user');

        return view('bookings.show', compact('booking'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Booking::class);

        $validated = $request->validate([
            'destination_id' => ['required', 'integer', Rule::exists('destinations', 'id')->whereNull('deleted_at')],
            'travel_date' => ['required', 'date', 'after:today'],
            'guests' => ['required', 'integer', 'min:1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $destination = Destinations::findOrFail($validated['destination_id']);

        // Owner, status and price are decided by the server, never by the form.
        $booking = $request->user()->bookings()->create([
            'destination_id' => $destination->id,
            'travel_date' => $validated['travel_date'],
            'guests' => $validated['guests'],
            'total_price' => $destination->price * $validated['guests'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        session()->flash('success', 'Booking submitted successfully');

        return redirect()->route('bookings.show', $booking);
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $booking->update(['status' => 'cancelled']);

        session()->flash('success', 'Booking cancelled');

        return redirect()->route('bookings.index');
    }
}
