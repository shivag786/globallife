<x-layouts.app title="Add Branch Manager" heading="Add Branch Manager">
    <form method="POST" action="{{ route('admin.branch-managers.store') }}" enctype="multipart/form-data">
        @include('admin.branch-managers._form')
    </form>
</x-layouts.app>
