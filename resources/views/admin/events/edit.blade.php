<x-layouts.app title="Edit Event" heading="Edit Event">
    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
        @include('admin.events._form')
    </form>
</x-layouts.app>
