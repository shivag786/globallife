<x-layouts.app title="Sub Admin Permissions" heading="Permissions">
    @php
        // Actions differ per module — managing accounts has nothing to publish or
        // export — so the tally counts what is actually on offer, not modules × 6.
        $moduleActions = [];
        $total = 0;
        $grantedCount = 0;

        foreach ($modules as $module) {
            $actions = \App\Services\PermissionMatrixService::actionsForModule($module);
            $moduleActions[$module] = $actions;
            $total += count($actions);

            foreach ($actions as $action) {
                if (in_array("{$module}.{$action}", $granted, true)) {
                    $grantedCount++;
                }
            }
        }

        // Every action that appears on at least one module, for the column toggles.
        $allActions = collect($moduleActions)->flatten()->unique()->values()->all();

        $actionHints = [
            'create' => 'Add new records',
            'edit' => 'Change existing records',
            'delete' => 'Remove records permanently',
            'publish' => 'Make records live on the public site',
            'approve' => 'Sign off records submitted by others',
            'export' => 'Download records as a file',
        ];

        $moduleIcons = [
            'branch-managers' => 'users',
            'blog' => 'tag',
            'products' => 'tag',
            'leads' => 'inbox',
            'events' => 'calendar',
            'media' => 'share',
            'testimonials' => 'star',
        ];

        $moduleHints = [
            'branch-managers' => 'Create and run their own Branch Managers',
        ];
    @endphp

    <div class="">
        {{-- Who this is for, and how much they currently hold. --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body text-white d-flex flex-wrap justify-content-between align-items-start gap-4"
                 style="background: linear-gradient(135deg,#1b3c2c,#0d2118);">
                <div class="min-w-0">
                    <p class="small text-uppercase fw-semibold mb-1" style="letter-spacing:.1em;color:#d4af37">Admin Access</p>
                    <h2 class="h3 fw-bold mb-1 text-truncate">{{ $subAdmin->name }}</h2>
                    <p class="small mb-0 text-truncate" style="color:#b9cfc2">{{ $subAdmin->email }}</p>
                </div>
                <div class="text-end">
                    <p class="small text-uppercase mb-1" style="color:#b9cfc2">Granted</p>
                    <p class="h2 fw-bold mb-0" style="color:#d4af37">
                        <span data-permission-count>{{ $grantedCount }}</span>
                        <span class="h5" style="color:#b9cfc2">/{{ $total }}</span>
                    </p>
                </div>
            </div>
            <div class="card-footer bg-body small text-muted">
                Set by Super Admin only. <strong>Branch Managers</strong> is the one that places this
                Sub Admin in the user hierarchy &mdash; with it they create and run their own Branch
                Managers, and see nobody else's.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sub-admins.permissions.update', $subAdmin) }}"
              data-permission-matrix style="max-width: 48rem">
            @csrf
            @method('PUT')

            {{-- Whole-grid toggle. Rows and columns have their own, below. --}}
            <div class="card border shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-semibold mb-0">Everything</p>
                        <p class="small text-muted mb-0">Turn every permission below on or off at once.</p>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="perm-all" data-permission-all>
                        <label class="form-check-label small fw-medium" for="perm-all">Select all</label>
                    </div>
                </div>
            </div>

            {{-- Per-action toggles: one action across every module that offers it. --}}
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <p class="small text-uppercase text-muted mb-2" style="letter-spacing:.08em">
                        Or by action, across all modules
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($allActions as $action)
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="perm-col-{{ $action }}" data-permission-column="{{ $action }}">
                                <label class="form-check-label small text-capitalize" for="perm-col-{{ $action }}">
                                    {{ $action }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- One card per module. --}}
            <div class="d-flex flex-column gap-3">
                @foreach ($modules as $module)
                    <div class="card border shadow-sm overflow-hidden">
                        <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <span class="d-flex align-items-center justify-content-center rounded flex-shrink-0"
                                      style="width:2.25rem;height:2.25rem;background:#e8f1ec;color:#2c704c">
                                    <x-icon name="{{ $moduleIcons[$module] ?? 'tag' }}" class="w-4 h-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="fw-semibold mb-0 text-capitalize">{{ str_replace('-', ' ', $module) }}</p>
                                    <p class="small text-muted mb-0">
                                        {{ $moduleHints[$module] ?? 'Their admin page for this area' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Row toggle: every action for this module. --}}
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="perm-row-{{ $module }}" data-permission-row="{{ $module }}"
                                       aria-label="All permissions for {{ $module }}">
                                <label class="form-check-label small fw-semibold" for="perm-row-{{ $module }}">
                                    Full access
                                </label>
                            </div>
                        </div>

                        {{-- One row per action, each with its own on/off switch. The
                             whole row is the label, so anywhere on it toggles. --}}
                        <div class="list-group list-group-flush">
                            @foreach ($moduleActions[$module] as $action)
                                @php $permission = "{$module}.{$action}"; @endphp
                                <label class="list-group-item list-group-item-action p-0" style="cursor:pointer">
                                    <input type="checkbox" name="permissions[{{ $permission }}]" value="1"
                                           data-permission-box data-action="{{ $action }}" data-module="{{ $module }}"
                                           class="perm-check visually-hidden"
                                           aria-label="{{ $action }} {{ $module }}"
                                           @checked(in_array($permission, $granted, true))>

                                    <span class="perm-row d-flex align-items-center justify-content-between gap-4 px-3 py-3">
                                        <span class="min-w-0">
                                            <span class="d-block fw-semibold small text-capitalize">{{ $action }}</span>
                                            <span class="d-block small text-muted">
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
                @endforeach
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
                <button type="submit" class="btn btn-primary">Save Permissions</button>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-link text-muted">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
