<x-layouts.app title="Employees" heading="Employees">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="width: 16rem"
                   placeholder="Search name, ID, email, mobile">
            <select name="department" class="form-select form-select-sm" style="width: 12rem">
                <option value="">All departments</option>
                @foreach ($departments as $d)
                    <option value="{{ $d->id }}" @selected((string) request('department') === (string) $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="width: 11rem">
                <option value="">Current employees</option>
                @foreach (\App\Models\Hr\Employee::STATUSES as $k => $l)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
                @endforeach
                <option value="all" @selected(request('status') === 'all')>Everyone</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </form>
        <div class="d-flex gap-2">
            @can('employees.export')
                <a href="{{ route('admin.hr.employees.export') }}" class="btn btn-sm btn-outline-secondary">Export CSV</a>
            @endcan
            @can('employees.create')
                <a href="{{ route('admin.hr.employees.create') }}" class="btn btn-brand btn-sm">+ Add Employee</a>
            @endcan
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Employee ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Department / Designation</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-top">
                            <td class="px-4 py-3 font-monospace">{{ $employee->employee_code }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $employee->name }}</td>
                            <td class="px-4 py-3">
                                {{ $employee->department?->name ?? '-' }}
                                <div class="text-secondary fs-xs">{{ $employee->designation?->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                {{ $employee->user?->email }}
                                <div class="text-secondary fs-xs">{{ $employee->user?->mobile }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $employee->joining_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">@include('hr.partials.badge', ['status' => $employee->employment_status, 'label' => $employee->statusLabel()])</td>
                            <td class="px-4 py-3 text-end text-nowrap">
                                <a href="{{ route('admin.hr.employees.show', $employee) }}" class="text-primary hover-underline">View</a>
                                @can('employees.edit')
                                    <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="text-primary hover-underline ms-3">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.app>
