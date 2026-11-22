<x-layouts.app title="Edit Employee" heading="Edit {{ $employee->name }} ({{ $employee->employee_code }})">
    <form method="POST" action="{{ route('admin.hr.employees.update', $employee) }}" enctype="multipart/form-data">
        @include('admin.hr.employees._form')
    </form>
</x-layouts.app>
