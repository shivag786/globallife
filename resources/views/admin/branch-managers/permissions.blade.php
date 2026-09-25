<x-layouts.app title="Branch Manager Permissions" heading="Permissions — {{ $manager->name }}">
    <p class="text-sm text-slate-500 mb-6 max-w-2xl">
        Controls which pages {{ $manager->name }} can see in their portal and what they can do there.
        Set by Super Admin only, and not passed down to their Commission Partners. Only modules that have
        a live page are listed &mdash; the Phase 2 names are left out until they exist.
    </p>

    <form method="POST" action="{{ route('admin.branch-managers.permissions.update', $manager) }}"
          class="bootstrap-scope max-w-2xl" data-permission-matrix>
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h6 mb-0 fw-semibold">Module permissions</h2>
                {{-- Whole-grid toggle. Each row and column has its own below. --}}
                <label class="d-inline-flex align-items-center gap-2 mb-0 small fw-medium text-slate-700">
                    <input type="checkbox" data-permission-all>
                    Select all
                </label>
            </div>
            <div class="card-body">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Module</th>
                            @foreach ($actions as $action)
                                <th class="text-center">
                                    <span class="text-capitalize d-block">{{ $action }}</span>
                                    {{-- Column toggle: this action across every module. --}}
                                    <label class="d-inline-flex align-items-center gap-1 mb-0 fw-normal small text-muted">
                                        <input type="checkbox" data-permission-column="{{ $action }}">
                                        all
                                    </label>
                                </th>
                            @endforeach
                            <th class="text-center">
                                <span class="d-block">Whole module</span>
                                <span class="fw-normal small text-muted">row</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr>
                                <td class="text-capitalize fw-medium">{{ str_replace('-', ' ', $module) }}</td>
                                @foreach ($actions as $action)
                                    @php $permission = "branch.{$module}.{$action}"; @endphp
                                    <td class="text-center">
                                        <input type="checkbox" name="permissions[{{ $permission }}]" value="1"
                                               data-permission-box data-action="{{ $action }}" data-module="{{ $module }}"
                                               aria-label="{{ $action }} {{ $module }}"
                                               @checked(in_array($permission, $granted, true))>
                                    </td>
                                @endforeach
                                {{-- Row toggle: every action for this module. --}}
                                <td class="text-center">
                                    <input type="checkbox" data-permission-row="{{ $module }}"
                                           aria-label="All permissions for {{ $module }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($actions) + 2 }}" class="text-center text-muted py-4">
                                    No modules are live yet, so there is nothing to grant.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex align-items-center gap-3">
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                Save Permissions
            </button>
            <a href="{{ route('admin.branch-managers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
</x-layouts.app>
