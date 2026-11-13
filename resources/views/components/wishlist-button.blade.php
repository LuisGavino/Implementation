
@props(['destination', 'size' => 'md'])

@php
    $isWishlisted = auth()->check()
        && auth()->user()->hasWishlisted($destination);

    $sizes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $sizeClass = $sizes[$size] ?? '';
@endphp

@auth
    <button
        type="button"
        class="btn btn-wishlist {{ $sizeClass }} {{ $isWishlisted ? 'wishlisted' : '' }}"
        data-destination-id="{{ $destination->id }}"
        data-url="{{ route('wishlist.toggle', $destination) }}"
        title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
        aria-label="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
        {{ $attributes }}
    >
        <i class="{{ $isWishlisted ? 'fas fa-heart' : 'far fa-heart' }}" aria-hidden="true"></i>
    </button>
@else
    <a
        href="{{ route('login') }}"
        class="btn btn-wishlist {{ $sizeClass }}"
        title="Login to add to wishlist"
        aria-label="Login to add to wishlist"
        {{ $attributes }}
    >
        <i class="far fa-heart" aria-hidden="true"></i>
    </a>
@endauth

@pushOnce('styles')
<style>
    .btn-wishlist {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        min-width: 34px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: #fff;
        color: #e74c3c;
        font-size: 17px;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        transition:
            transform 0.25s ease,
            color 0.25s ease,
            background-color 0.25s ease,
            box-shadow 0.25s ease;
    }

    .btn-wishlist i {
        display: inline-block;
        visibility: visible;
        opacity: 1;
        line-height: 1;
    }

    .btn-wishlist:hover,
    .btn-wishlist:focus-visible {
        color: #c0392b;
        background-color: #fff0f0;
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.35);
    }

    .btn-wishlist:focus-visible {
        outline: 2px solid #e74c3c;
        outline-offset: 3px;
    }

    .btn-wishlist.wishlisted {
        color: #e74c3c;
    }

    .btn-wishlist:hover i {
        animation: wishlist-heartbeat 0.4s ease-in-out;
    }

    @keyframes wishlist-heartbeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.2); }
        50% { transform: scale(1.05); }
        75% { transform: scale(1.2); }
    }

    @media (prefers-reduced-motion: reduce) {
        .btn-wishlist,
        .btn-wishlist:hover i {
            transition: none;
            animation: none;
        }
    }
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-wishlist[data-url]').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();

            if (button.dataset.loading === 'true') {
                return;
            }

            const icon = button.querySelector('i');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            if (!icon || !csrfMeta) {
                console.error('Wishlist icon or CSRF token is missing.');
                return;
            }

            button.dataset.loading = 'true';

            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Wishlist request failed: ' + response.status);
                }

                const data = await response.json();

                if (typeof data.wishlisted !== 'boolean') {
                    throw new Error('The server did not return a valid wishlist status.');
                }

                if (data.wishlisted) {
                    icon.classList.remove('far', 'fa-heart-o');
                    icon.classList.add('fas', 'fa-heart');

                    button.classList.add('wishlisted');
                    button.title = 'Remove from wishlist';
                    button.setAttribute('aria-label', 'Remove from wishlist');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far', 'fa-heart');

                    button.classList.remove('wishlisted');
                    button.title = 'Add to wishlist';
                    button.setAttribute('aria-label', 'Add to wishlist');
                }
            } catch (error) {
                console.error('Wishlist error:', error);
            } finally {
                button.dataset.loading = 'false';
            }
        });
    });
});
</script>
@endPushOnce
