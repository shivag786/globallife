<x-layouts.app title="Edit Branch Manager" heading="Edit Branch Manager">
    <form method="POST" action="{{ route('admin.branch-managers.update', $manager) }}">
        @include('admin.branch-managers._form')
    </form>
</x-layouts.app>
