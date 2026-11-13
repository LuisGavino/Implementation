
@extends('layouts.front')

@section('content')

{{-- Wishlist Hero --}}
<section class="wishlist-hero"
    style="background-image: url('{{ asset('images/bg_1.jpg') }}');">
    <div class="wishlist-hero-overlay"></div>

    <div class="container wishlist-hero-content">

        <h1>My Wishlist</h1>

        <p>Keep your dream destinations close and your next adventure closer.</p>

        <nav class="wishlist-breadcrumb" aria-label="breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <i class="fa fa-angle-right"></i>
            <span>My Wishlist</span>
        </nav>
    </div>
</section>

{{-- Wishlist Content --}}
<section class="wishlist-section">
    <div class="container">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="wishlist-alert" role="status">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button"
                    onclick="this.parentElement.remove()"
                    aria-label="Dismiss message">&times;</button>
            </div>
        @endif

        @if($wishlisted->isEmpty())

            {{-- Empty Wishlist --}}
            <div class="wishlist-empty">
                <div class="empty-heart">
                    <i class="fa fa-heart-o"></i>
                </div>

                <span class="empty-eyebrow">YOUR NEXT ADVENTURE AWAITS</span>

                <h2>Your wishlist is waiting for a little love.</h2>

                <p>
                    You haven't saved any destinations yet. Explore beautiful
                    places and save the ones you'd love to visit.
                </p>

                <a href="{{ route('packages') }}" class="wishlist-primary-btn">
                    <i class="fa fa-compass"></i>
                    Explore Destinations
                    <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        @else

            {{-- Wishlist Heading --}}
            <div class="wishlist-heading">
                <div>
                    <span class="section-eyebrow">SAVED FOR LATER</span>
                    <h2>Your dream destinations</h2>
                    <p>All the places you've fallen in love with, in one place.</p>
                </div>

                <div class="wishlist-count">
                    <i class="fa fa-heart"></i>
                    <span>
                        <strong>{{ $wishlisted->total() }}</strong>
                        {{ $wishlisted->total() === 1 ? 'destination' : 'destinations' }}
                    </span>
                </div>
            </div>

            {{-- Destination Cards --}}
            <div class="wishlist-grid">
                @foreach($wishlisted as $destination)
                    <article class="wishlist-card">

                        {{-- Destination Image --}}
                        <a href="{{ route('desti.show', $destination) }}"
                           class="wishlist-card-image"
                           aria-label="View {{ $destination->title }}">

                            <img
                                src="{{ $destination->image
                                    ? asset('storage/' . $destination->image)
                                    : asset('images/bg_1.jpg') }}"
                                alt="{{ $destination->title }}"
                                loading="lazy">

                            <span class="saved-badge">
                                <i class="fa fa-heart"></i> Saved
                            </span>

                            <span class="image-view">
                                <i class="fa fa-arrow-up-right-from-square"></i>
                            </span>
                        </a>

                        {{-- Card Details --}}
                        <div class="wishlist-card-body">

                            <div class="wishlist-card-top">
                                <div class="wishlist-rating">
                                    <i class="fa fa-star"></i>
                                    <strong>{{ number_format((float) ($destination->average_rating ?? 0), 1) }}</strong>
                                    <span>({{ $destination->reviews_count ?? 0 }} reviews)</span>
                                </div>
                            </div>

                            <h3>
                                <a href="{{ route('desti.show', $destination) }}">
                                    {{ $destination->title }}
                                </a>
                            </h3>

                            <p class="wishlist-description">
                                {{ \Illuminate\Support\Str::limit($destination->description, 105) }}
                            </p>

                            <div class="wishlist-card-divider"></div>

                            <div class="wishlist-card-footer">
                                <div class="wishlist-price">
                                    <span>Starting from</span>
                                    <strong>${{ number_format((float) $destination->pricing, 2) }}</strong>
                                </div>

                                <form
                                    action="{{ route('wishlist.destroy', $destination) }}"
                                    method="POST"
                                    onsubmit="return confirm('Remove this destination from your wishlist?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="wishlist-remove"
                                        aria-label="Remove {{ $destination->title }} from wishlist"
                                        title="Remove from wishlist">
                                        <i class="fa fa-heart"></i>
                                    </button>
                                </form>
                            </div>

                            <a href="{{ route('desti.show', $destination) }}"
                               class="wishlist-details-btn">
                                Explore Destination
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($wishlisted->hasPages())
                <div class="wishlist-pagination">
                    {{ $wishlisted->links() }}
                </div>
            @endif

        @endif
    </div>
</section>

@pushOnce('styles')
<style>
    /* Hero */
    .wishlist-hero {
        position: relative;
        min-height: 390px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        color: #fff;
        overflow: hidden;
    }

    .wishlist-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            110deg,
            rgba(8, 32, 48, .88),
            rgba(8, 32, 48, .48)
        );
    }

    .wishlist-hero-content {
        position: relative;
        z-index: 1;
        text-align: center;
        padding-top: 65px;
        padding-bottom: 50px;
    }

    .wishlist-eyebrow,
    .section-eyebrow,
    .empty-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
    }

    .wishlist-eyebrow {
        padding: 9px 15px;
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 30px;
        background: rgba(255,255,255,.08);
    }

    .wishlist-eyebrow i {
        margin-right: 6px;
        color: #ff8290;
    }

    .wishlist-hero h1 {
        margin: 20px 0 12px;
        color: #fff;
        font-size: clamp(38px, 5vw, 58px);
        font-weight: 800;
        letter-spacing: -1.5px;
    }

    .wishlist-hero-content > p {
        margin-bottom: 22px;
        color: rgba(255,255,255,.85);
        font-size: 15px;
    }

    .wishlist-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 13px;
    }

    .wishlist-breadcrumb a {
        color: #fff;
        text-decoration: none;
    }

    .wishlist-breadcrumb a:hover {
        color: #ffb4bd;
    }

    .wishlist-breadcrumb i {
        font-size: 11px;
        opacity: .7;
    }

    .wishlist-breadcrumb span {
        color: #ffc0c7;
    }

    /* Main section */
    .wishlist-section {
        min-height: 450px;
        padding: 75px 0 90px;
        background: #f7f9fc;
    }

    .wishlist-alert {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 30px;
        padding: 16px 20px;
        border: 1px solid #bce8d0;
        border-radius: 12px;
        background: #edfff4;
        color: #176b3a;
    }

    .wishlist-alert > i {
        font-size: 20px;
    }

    .wishlist-alert button {
        margin-left: auto;
        border: none;
        background: transparent;
        color: inherit;
        font-size: 24px;
        cursor: pointer;
    }

    /* Heading */
    .wishlist-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        margin-bottom: 32px;
    }

    .section-eyebrow {
        margin-bottom: 9px;
        color: #e45b68;
    }

    .wishlist-heading h2 {
        margin: 0 0 8px;
        color: #192b3a;
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 800;
        letter-spacing: -.7px;
    }

    .wishlist-heading p {
        margin: 0;
        color: #788595;
        font-size: 14px;
    }

    .wishlist-count {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        padding: 13px 18px;
        border: 1px solid #f0dfe2;
        border-radius: 14px;
        background: #fff;
        color: #4b5968;
        font-size: 13px;
    }

    .wishlist-count > i {
        display: flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #fff0f1;
        color: #e45b68;
        font-size: 17px;
    }

    .wishlist-count strong {
        color: #192b3a;
        font-size: 17px;
    }

    /* Cards grid */
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 27px;
    }

    .wishlist-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid #edf0f4;
        border-radius: 19px;
        background: #fff;
        box-shadow: 0 5px 20px rgba(25, 43, 58, .045);
        transition: transform .3s ease, box-shadow .3s ease;
    }

    .wishlist-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 18px 38px rgba(25, 43, 58, .12);
    }

    .wishlist-card-image {
        position: relative;
        display: block;
        height: 230px;
        overflow: hidden;
        background: #e8edf2;
    }

    .wishlist-card-image::after {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(0,0,0,.12),
            transparent 35%,
            rgba(0,0,0,.12)
        );
        content: "";
        pointer-events: none;
    }

    .wishlist-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .55s ease;
    }

    .wishlist-card:hover .wishlist-card-image img {
        transform: scale(1.07);
    }

    .saved-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 30px;
        background: rgba(255,255,255,.96);
        color: #dc4c5c;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(0,0,0,.08);
    }

    .image-view {
        position: absolute;
        right: 15px;
        bottom: 15px;
        z-index: 1;
        display: flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255,255,255,.95);
        color: #243746;
        opacity: 0;
        transform: translateY(5px);
        transition: opacity .25s ease, transform .25s ease;
    }

    .wishlist-card:hover .image-view {
        opacity: 1;
        transform: translateY(0);
    }

    /* Card body */
    .wishlist-card-body {
        padding: 22px;
    }

    .wishlist-card-top {
        margin-bottom: 11px;
    }

    .wishlist-rating {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
    }

    .wishlist-rating > i {
        color: #f5ad42;
    }

    .wishlist-rating strong {
        color: #344454;
    }

    .wishlist-rating span {
        color: #929dab;
    }

    .wishlist-card h3 {
        margin: 0 0 10px;
        font-size: 21px;
        font-weight: 750;
        line-height: 1.35;
    }

    .wishlist-card h3 a {
        color: #1b3041;
        text-decoration: none;
        transition: color .2s ease;
    }

    .wishlist-card h3 a:hover {
        color: #e45b68;
    }

    .wishlist-description {
        min-height: 63px;
        margin-bottom: 17px;
        color: #7a8794;
        font-size: 13px;
        line-height: 1.7;
    }

    .wishlist-card-divider {
        height: 1px;
        margin-bottom: 17px;
        background: #edf0f4;
    }

    .wishlist-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .wishlist-price {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .wishlist-price span {
        color: #8995a1;
        font-size: 11px;
    }

    .wishlist-price strong {
        color: #192f40;
        font-size: 22px;
        font-weight: 800;
    }

    .wishlist-remove {
        display: flex;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid #f3d9dc;
        border-radius: 12px;
        background: #fff;
        color: #e45b68;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .wishlist-remove:hover {
        transform: scale(1.06);
        border-color: #e45b68;
        background: #e45b68;
        color: #fff;
    }

    .wishlist-details-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        padding: 13px 16px;
        border: 1px solid #e8edf2;
        border-radius: 11px;
        background: #f8fafc;
        color: #253d50;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .wishlist-details-btn:hover {
        border-color: #253d50;
        background: #253d50;
        color: #fff;
    }

    /* Empty state */
    .wishlist-empty {
        max-width: 620px;
        margin: 0 auto;
        padding: 55px 25px 65px;
        text-align: center;
        border: 1px solid #edf0f4;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 10px 35px rgba(25, 43, 58, .05);
    }

    .empty-heart {
        display: flex;
        width: 94px;
        height: 94px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        border-radius: 28px;
        background: #fff0f1;
        color: #e45b68;
        font-size: 37px;
    }

    .empty-eyebrow {
        color: #e45b68;
    }

    .wishlist-empty h2 {
        margin: 13px auto;
        max-width: 460px;
        color: #192b3a;
        font-size: 29px;
        font-weight: 800;
        line-height: 1.3;
    }

    .wishlist-empty > p {
        max-width: 440px;
        margin: 0 auto 25px;
        color: #7b8794;
        font-size: 14px;
        line-height: 1.8;
    }

    .wishlist-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 14px 22px;
        border-radius: 11px;
        background: #e45b68;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(228, 91, 104, .2);
        transition: background .2s ease, transform .2s ease;
    }

    .wishlist-primary-btn:hover {
        transform: translateY(-2px);
        background: #c94756;
        color: #fff;
    }

    /* Pagination */
    .wishlist-pagination {
        display: flex;
        justify-content: center;
        margin-top: 45px;
    }

    .wishlist-pagination nav {
        display: flex;
        justify-content: center;
    }

    /* Responsive layout */
    @media (max-width: 991px) {
        .wishlist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .wishlist-card-image {
            height: 210px;
        }

        .wishlist-card-body {
            padding: 18px;
        }
    }

    @media (max-width: 575px) {
        .wishlist-hero {
            min-height: 330px;
        }

        .wishlist-section {
            padding: 45px 0 60px;
        }

        .wishlist-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 18px;
        }

        .wishlist-grid {
            grid-template-columns: 1fr;
            gap: 22px;
        }

        .wishlist-card-image {
            height: 235px;
        }

        .wishlist-empty {
            padding: 40px 20px;
        }

        .wishlist-empty h2 {
            font-size: 24px;
        }

        .image-view {
            opacity: 1;
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .wishlist-card,
        .wishlist-card-image img,
        .image-view,
        .wishlist-remove,
        .wishlist-details-btn,
        .wishlist-primary-btn {
            transition: none;
        }
    }
</style>
@endPushOnce

@endsection
