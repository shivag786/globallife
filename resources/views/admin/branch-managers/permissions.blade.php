<x-layouts.app title="Branch Manager Permissions" heading="Permissions">
    @php
        $total = count($modules) * count($actions);
        $grantedCount = 0;
        foreach ($modules as $m) {
            foreach ($actions as $a) {
                if (in_array("branch.{$m}.{$a}", $granted, true)) {
                    $grantedCount++;
                }
            }
        }

        // A one-line explanation per action, so "delete" is never a guess.
        $actionHints = [
            'view' => 'Open the page and read it',
            'edit' => 'Add and change records',
            'delete' => 'Remove records permanently',
        ];

        $moduleIcons = ['commission-partners' => 'users', 'leads' => 'inbox'];
    @endphp

    {{-- Who this is for, and how much they currently hold. --}}
    <div class="rounded-4 overflow-hidden premium-shadow mb-6" style="background: linear-gradient(135deg,#1b3c2c,#0d2118);">
        <div class="px-6 py-5 px-sm-8 py-sm-6 d-flex flex-wrap align-items-start justify-content-between gap-5">
            <div class="min-w-0">
                <p class="fs-xs text-uppercase tracking-widest text-gold-400 fw-semibold">Portal Access</p>
                <h2 class="font-display fs-2xl fs-3xl fw-bold text-white mt-1 text-truncate">{{ $manager->name }}</h2>
                <p class="fs-sm text-brand- mt-1 text-truncate">{{ $manager->email }}</p>
            </div>

            <div class="text-end">
                <p class="fs-xs text-uppercase tracking-wider text-brand-">Granted</p>
                <p class="font-display fs-3xl fw-bold text-gold-400 mt-1">
                    <span data-permission-count>{{ $grantedCount }}</span><span class="text-brand- fs-xl">/{{ $total }}</span>
                </p>
            </div>
        </div>

        <p class="px-6 px-sm-8 py-3 border-top border-white border-opacity-10 fs-xs text-brand-">
            Set by Super Admin only, and never passed down to their Commission Partners.
            Modules without a live page are not listed.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.branch-managers.permissions.update', $manager) }}"
          data-permission-matrix class="mw-3xl">
        @csrf
        @method('PUT')

        {{-- Whole-grid toggle. Rows and columns have their own, below. --}}
        <div class="bg-body rounded-3 border px-5 py-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="fw-semibold text-body">Everything</p>
                <p class="fs-xs text-secondary">Turn every permission below on or off at once.</p>
            </div>
            <label class="d-inline-flex align-items-center gap-2 user-select-none">
                <input type="checkbox" data-permission-all class="visually-hidden">
                <span class="px-4 py-2 rounded-pill fs-sm fw-semibold border text-body-secondary">
                    Select all
                </span>
            </label>
        </div>

        {{-- Per-action toggles: one action across every module. --}}
        @if (count($modules) > 1)
            <div class="bg-body rounded-3 border px-5 py-4 mb-4">
                <p class="fs-xs text-uppercase tracking-wider text-secondary mb-2">Or by action, across all modules</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($actions as $action)
                        <label class="d-inline-flex align-items-center gap-2 user-select-none">
                            <input type="checkbox" data-permission-column="{{ $action }}" class="visually-hidden">
                            <span class="text-capitalize px-3 py-2 rounded-pill fs-sm fw-medium border text-body-secondary">
                                {{ $action }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Kept for the single-module case so the column toggles still exist. --}}
            <div class="d-none">
                @foreach ($actions as $action)
                    <input type="checkbox" data-permission-column="{{ $action }}">
                @endforeach
            </div>
        @endif

        {{-- One card per module. --}}
        <div class="d-flex flex-column gap-4">
            @forelse ($modules as $module)
                <div class="bg-body rounded-3 border overflow-hidden">
                    <div class="px-5 py-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <span class="d-flex h-9 w-9 flex-shrink-0 align-items-center justify-content-center rounded-2 bg-brand-subtle text-brand">
                                <x-icon name="{{ $moduleIcons[$module] ?? 'tag' }}" class="w-4 h-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="fw-semibold text-body text-capitalize">{{ str_replace('-', ' ', $module) }}</p>
                                <p class="fs-xs text-secondary">Their portal page for this area</p>
                            </div>
                        </div>

                        {{-- Row toggle: every action for this module. --}}
                        <label class="d-inline-flex align-items-center gap-2 user-select-none">
                            <input type="checkbox" data-permission-row="{{ $module }}" class="visually-hidden"
                                   aria-label="All permissions for {{ $module }}">
                            <span class="px-3 py-2 rounded-pill fs-xs fw-semibold border text-body-secondary">
                                Full access
                            </span>
                        </label>
                    </div>

                    {{-- One row per action, each with its own on/off switch. The
                         whole row is the label, so anywhere on it toggles. --}}
                    <div class="divide-y">
                        @foreach ($actions as $action)
                            @php $permission = "branch.{$module}.{$action}"; @endphp
                            <label class="d-block user-select-none">
                                <input type="checkbox" name="permissions[{{ $permission }}]" value="1"
                                       data-permission-box data-action="{{ $action }}" data-module="{{ $module }}"
                                       class="perm-check visually-hidden"
                                       aria-label="{{ $action }} {{ $module }}"
                                       @checked(in_array($permission, $granted, true))>

                                <span class="perm-row d-flex align-items-center justify-content-between gap-4 px-5 py-3">
                                    <span class="min-w-0">
                                        <span class="d-block fw-semibold text-body text-capitalize fs-sm">{{ $action }}</span>
                                        <span class="d-block fs-xs text-secondary mt-1">
                                            {{ $actionHints[$action] ?? 'Allow this action' }}
                                        </span>
                                    </span>

                                    <span class="perm-track" aria-hidden="true">
                                        <span class="perm-knob"></span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-body rounded-3 border px-5 py-10 text-center">
                    <p class="text-secondary">No modules are live yet, so there is nothing to grant.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 d-flex flex-wrap align-items-center gap-3">
            <button type="submit" class="btn btn-brand btn-sm">
                Save Permissions
            </button>
            <a href="{{ route('admin.branch-managers.index') }}" class="fs-sm text-secondary">Cancel</a>
        </div>
    </form>
</x-layouts.app>
