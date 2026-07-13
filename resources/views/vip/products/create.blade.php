<x-layouts.app title="Add Product" heading="Add Product">
    <form method="POST" action="{{ route('vip.products.store') }}" enctype="multipart/form-data">
        @include('vip.products._form')
    </form>
</x-layouts.app>
