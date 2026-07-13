<x-layouts.app title="Add Service" heading="Add Service">
    <form method="POST" action="{{ route('vip.services.store') }}" enctype="multipart/form-data">
        @include('vip.services._form')
    </form>
</x-layouts.app>
