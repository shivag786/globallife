<x-layouts.app title="Add Event" heading="Add Event">
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @include('admin.events._form')
    </form>
</x-layouts.app>
