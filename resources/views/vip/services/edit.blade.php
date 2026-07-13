<x-layouts.app title="Edit Service" heading="Edit Service">
    <form method="POST" action="{{ route('vip.services.update', $service) }}" enctype="multipart/form-data">
        @include('vip.services._form')
    </form>
</x-layouts.app>
