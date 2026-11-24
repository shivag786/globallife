{{-- Heart button that saves a VIP business's own product to the wishlist
     (WishlistService business list). Expects $product and $inWishlist. --}}
<form method="POST" action="{{ route($inWishlist ? 'wishlist.remove' : 'wishlist.add') }}" class="{{ $class ?? 'position-absolute top-3 end-3' }}">
    @csrf
    @if ($inWishlist) @method('DELETE') @endif
    <input type="hidden" name="business_product_id" value="{{ $product->id }}">
    <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 rounded-pill bg-white shadow-sm d-flex align-items-center justify-content-center {{ $inWishlist ? 'text-danger' : 'text-secondary' }}"
            aria-label="{{ $inWishlist ? 'Remove from wishlist' : 'Save to wishlist' }}" title="{{ $inWishlist ? 'Remove from wishlist' : 'Save to wishlist' }}">
        <x-icon name="heart" class="w-4 h-4" :filled="$inWishlist" />
    </button>
</form>
