<x-layouts.app title="Sub Admins" heading="Sub Admins">
    <div class="mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <p class="small text-muted mb-0" style="max-width: 46rem">
                A Sub Admin sits between you and the Branch Managers. Grant them the
                <strong>Branch Managers</strong> module and they can create and run their own &mdash;
                each Sub Admin sees only the ones they created.
            </p>
            <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary btn-sm text-nowrap">
                + Add Sub Admin
            </a>
        </div>
    </div>

    <div class="">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Branch Managers</th>
                            <th>Modules</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subAdmins as $subAdmin)
                            @php
                                $granted = \App\Services\PermissionMatrixService::groupByModule(
                                    $subAdmin->getAllPermissions()->pluck('name')
                                );
                                $activeModules = array_keys(array_filter($granted, fn ($actions) => $actions !== []));
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ $subAdmin->name }}</td>
                                <td>{{ $subAdmin->email }}</td>
                                <td>{{ $subAdmin->mobile ?: '—' }}</td>
                                <td>{{ $subAdmin->branch_managers_count }}</td>
                                <td>
                                    @forelse ($activeModules as $module)
                                        <span class="badge rounded-pill bg-body-tertiary text-body border me-1">
                                            {{ str_replace('-', ' ', $module) }}
                                        </span>
                                    @empty
                                        <span class="text-muted small">Nothing granted yet</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $subAdmin->status === 'active' ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ $subAdmin->status }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.sub-admins.permissions.edit', $subAdmin) }}"
                                       class="btn btn-sm btn-outline-secondary">Permissions</a>
                                    <a href="{{ route('admin.sub-admins.edit', $subAdmin) }}"
                                       class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('admin.sub-admins.toggle-status', $subAdmin) }}" method="POST" class="d-inline"
                                          data-confirm="{{ $subAdmin->status === 'active' ? "Suspend {$subAdmin->name}? They will be signed out and cannot log back in." : "Reactivate {$subAdmin->name}?" }}"
                                          data-confirm-title="{{ $subAdmin->status === 'active' ? 'Suspend Sub Admin' : 'Activate Sub Admin' }}"
                                          data-confirm-button="Yes, continue">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            {{ $subAdmin->status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No Sub Admins yet. Add one to start delegating Branch Managers.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
