<x-layouts.app title="Add Employee" heading="Add Employee">
    <form method="POST" action="{{ route('admin.hr.employees.store') }}" enctype="multipart/form-data">
        @include('admin.hr.employees._form', ['employee' => null])
    </form>
</x-layouts.app>
