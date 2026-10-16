@php $cartCount = $cartCount ?? app(\App\Services\CartService::class)->count(); @endphp
<a href="{{ route('cart.index') }}" data-cart-icon class="position-relative text-body-secondary" aria-label="Cart">
    <x-icon name="shopping-bag" class="w-6 h-6" />
    <span data-cart-count class="position-absolute top-1 end-1 bg-brand text-white fw-bold rounded-pill min-w-4 h-4 px-1 d-flex align-items-center justify-content-center {{ $cartCount > 0 ? '' : 'd-none' }}">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
</a>
