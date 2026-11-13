@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-suitcase-rolling me-2" style="color:var(--admin-primary)"></i>All Bookings</h5>
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Booking::STATUSES as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        @if ($bookings->count() > 0)
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Destination</th>
                    <th>Travel date</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                <tr>
                    <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->id }}</a></td>
                    <td><strong>{{ $booking->user->name ?? 'Deleted user' }}</strong><br><small>{{ $booking->user->email ?? '' }}</small></td>
                    <td>{{ $booking->destination->title ?? 'Unavailable' }}</td>
                    <td>{{ $booking->travel_date->format('M j, Y') }}</td>
                    <td>{{ $booking->guests }}</td>
                    <td>₱{{ number_format($booking->total_price, 2) }}</td>
                    <td>
                        <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="d-flex gap-1">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="min-width:120px">
                                @foreach (\App\Booking::STATUSES as $s)
                                    <option value="{{ $s }}" @selected($booking->status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-admin-sm btn-admin-edit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST"
                              onsubmit="return confirm('Delete this booking?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-admin-sm btn-admin-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">{{ $bookings->links('pagination::bootstrap-5') }}</div>
        @else
        <div class="admin-empty">
            <i class="fas fa-suitcase-rolling"></i>
            <h5>No bookings found</h5>
        </div>
        @endif
    </div>
</div>
@endsection
