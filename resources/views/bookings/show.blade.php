@extends('layouts.front')

@section('title', 'Booking #' . $booking->id . ' - Lakbay Pilipinas')

@section('page')
@include('partials.navbar')

<!-- Page Hero -->
<section class="tt-page-hero">
	<div class="tt-page-hero-bg" style="background-image: url('{{ asset('images/bg_1.jpg') }}');"></div>
	<div class="container">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb justify-content-center">
				<li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>Home</a></li>
				<li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">My Bookings</a></li>
				<li class="breadcrumb-item active">Booking #{{ $booking->id }}</li>
			</ol>
		</nav>
		<h1 class="tt-page-title">Booking <span class="accent">#{{ $booking->id }}</span></h1>
	</div>
</section>

<section class="tt-section-sm">
	<div class="container">

		<div class="card shadow-sm">
			<div class="card-body">
				<h4 class="mb-3">{{ $booking->destination->title ?? 'Destination unavailable' }}</h4>
				<dl class="row mb-0">
					<dt class="col-sm-3">Booked by</dt>
					<dd class="col-sm-9">{{ $booking->user->name }}</dd>

					<dt class="col-sm-3">Travel date</dt>
					<dd class="col-sm-9">{{ $booking->travel_date->format('F j, Y') }}</dd>

					<dt class="col-sm-3">Guests</dt>
					<dd class="col-sm-9">{{ $booking->guests }}</dd>

					<dt class="col-sm-3">Total price</dt>
					<dd class="col-sm-9">₱{{ number_format($booking->total_price, 2) }}</dd>

					<dt class="col-sm-3">Status</dt>
					<dd class="col-sm-9"><span class="badge bg-secondary text-capitalize">{{ $booking->status }}</span></dd>

					@if ($booking->notes)
						<dt class="col-sm-3">Notes</dt>
						<dd class="col-sm-9">{{ $booking->notes }}</dd>
					@endif
				</dl>

				<div class="mt-4 d-flex gap-2">
					<a href="{{ route('bookings.index') }}" class="btn-tt-outline">Back to my bookings</a>

					@can('cancel', $booking)
						<form action="{{ route('bookings.cancel', $booking) }}" method="POST"
							  onsubmit="return confirm('Cancel this booking?');">
							@csrf
							@method('PATCH')
							<button type="submit" class="btn btn-outline-danger">Cancel booking</button>
						</form>
					@endcan
				</div>
			</div>
		</div>
	</div>
</section>

@include('partials.footer')
@endsection
