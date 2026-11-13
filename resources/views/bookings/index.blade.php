@extends('layouts.front')

@section('title', 'My Bookings - Lakbay Pilipinas')

@section('page')
@include('partials.navbar')

<!-- Page Hero -->
<section class="tt-page-hero">
	<div class="tt-page-hero-bg" style="background-image: url('{{ asset('images/bg_1.jpg') }}');"></div>
	<div class="container">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb justify-content-center">
				<li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>Home</a></li>
				<li class="breadcrumb-item active">My Bookings</li>
			</ol>
		</nav>
		<h1 class="tt-page-title">My <span class="accent">Bookings</span></h1>
		<p class="tt-page-subtitle">Your trips, all in one place.</p>
	</div>
</section>

<section class="tt-section-sm">
	<div class="container">

		<div class="row g-4">
			<!-- Own bookings -->
			<div class="col-lg-8">
				<h4 class="mb-3">Your trips</h4>

				@forelse ($bookings as $booking)
					<div class="card mb-3 shadow-sm">
						<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
							<div>
								<h5 class="mb-1">{{ $booking->destination->title ?? 'Destination unavailable' }}</h5>
								<small class="text-muted">
									{{ $booking->travel_date->format('M j, Y') }} &middot;
									{{ $booking->guests }} {{ $booking->guests === 1 ? 'guest' : 'guests' }} &middot;
									₱{{ number_format($booking->total_price, 2) }}
								</small>
							</div>
							<div class="d-flex align-items-center gap-2">
								<span class="badge bg-secondary text-capitalize">{{ $booking->status }}</span>
								<a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">View</a>
							</div>
						</div>
					</div>
				@empty
					<div class="alert alert-info">You have no bookings yet. Use the form to book your first trip.</div>
				@endforelse

				{{ $bookings->links('pagination::bootstrap-5') }}
			</div>

			<!-- New booking -->
			<div class="col-lg-4">
				<div class="card shadow-sm">
					<div class="card-body">
						<h5 class="mb-3">Book a trip</h5>

						<form action="{{ route('bookings.store') }}" method="POST">
							@csrf
							<div class="mb-3">
								<label for="destination_id" class="form-label">Destination</label>
								<select name="destination_id" id="destination_id" class="form-select" required>
									<option value="">Choose a destination</option>
									@foreach ($destinations as $destination)
										<option value="{{ $destination->id }}" @selected(old('destination_id') == $destination->id)>
											{{ $destination->title }} (₱{{ number_format($destination->price) }} / person)
										</option>
									@endforeach
								</select>
							</div>
							<div class="mb-3">
								<label for="travel_date" class="form-label">Travel date</label>
								<input type="date" name="travel_date" id="travel_date" class="form-control"
									   value="{{ old('travel_date') }}" min="{{ now()->addDay()->toDateString() }}" required>
							</div>
							<div class="mb-3">
								<label for="guests" class="form-label">Guests</label>
								<input type="number" name="guests" id="guests" class="form-control"
									   value="{{ old('guests', 1) }}" min="1" max="50" required>
							</div>
							<div class="mb-3">
								<label for="notes" class="form-label">Notes (optional)</label>
								<textarea name="notes" id="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
							</div>
							<button type="submit" class="btn-tt-primary w-100">Submit booking</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

@include('partials.footer')
@endsection
