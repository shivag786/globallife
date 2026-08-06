@php $cartCount = $cartCount ?? app(\App\Services\CartService::class)->count(); @endphp
<a href="{{ route('cart.index') }}" data-cart-icon class="relative text-slate-600 hover:text-brand-700 transition" aria-label="Cart">
    <x-icon name="shopping-bag" class="w-6 h-6" />
    <span data-cart-count class="absolute -top-1.5 -right-1.5 bg-brand-700 text-white text-[0.6rem] font-bold rounded-full min-w-4 h-4 px-1 flex items-center justify-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
</a>
