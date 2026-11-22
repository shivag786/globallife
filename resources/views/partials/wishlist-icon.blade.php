@php $wishlistCount = app(\App\Services\WishlistService::class)->count(); @endphp
<a href="{{ route('wishlist.index') }}" class="position-relative text-body-secondary {{ request()->routeIs('wishlist.*') ? 'text-danger' : '' }}"
   aria-label="Wishlist" title="Wishlist">
    <x-icon name="heart" class="w-6 h-6" :filled="request()->routeIs('wishlist.*')" />
    @if ($wishlistCount > 0)
        <span class="position-absolute top-1 end-1 bg-danger text-white fw-bold rounded-pill min-w-4 h-4 px-1 d-flex align-items-center justify-content-center">{{ $wishlistCount > 9 ? '9+' : $wishlistCount }}</span>
    @endif
</a>
