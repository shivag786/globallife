<x-layouts.app title="Branch Managers" heading="Branch Managers">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.branch-managers.create') }}" class="btn btn-brand btn-sm">
            + Add Branch Manager
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Branches (Cities)</th>
                    <th class="px-4 py-3">Commission Cap</th>
                    <th class="px-4 py-3">Commission Partners</th>
                    @if ($seesAll)
                        <th class="px-4 py-3">Managed By</th>
                    @endif
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($managers as $manager)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $manager->name }}</td>
                        <td class="px-4 py-3">{{ $manager->email }}</td>
                        <td class="px-4 py-3">{{ $manager->branchCities->pluck('name')->implode(', ') }}</td>
                        <td class="px-4 py-3">{{ $manager->commission_percentage }}%</td>
                        <td class="px-4 py-3">{{ $manager->commission_partners_count }}</td>
                        @if ($seesAll)
                            <td class="px-4 py-3">
                                @if ($manager->creator)
                                    {{ $manager->creator->name }}
                                @else
                                    <span class="text-secondary">Super Admin</span>
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $manager->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $manager->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('admin.branch-managers.permissions.edit', $manager) }}" class="text-primary hover-underline">Permissions</a>
                            <a href="{{ route('admin.branch-managers.edit', $manager) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('admin.branch-managers.toggle-status', $manager) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-warning hover-underline">
                                    {{ $manager->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $seesAll ? 8 : 7 }}" class="px-4 py-6 text-center text-secondary">
                            {{ $seesAll ? 'No branch managers yet.' : "You haven't added any Branch Managers yet." }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
