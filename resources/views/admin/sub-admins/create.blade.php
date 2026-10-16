<x-layouts.app title="Add Sub Admin" heading="Add Sub Admin">
    <form method="POST" action="{{ route('admin.sub-admins.store') }}">
        @include('admin.sub-admins._form')
    </form>
</x-layouts.app>
