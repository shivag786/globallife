<x-layouts.app title="Edit Product" heading="Edit Product">
    <form method="POST" action="{{ route('vip.products.update', $product) }}" enctype="multipart/form-data">
        @include('vip.products._form')
    </form>
</x-layouts.app>
