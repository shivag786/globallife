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
    <div class="rounded-2xl overflow-hidden premium-shadow mb-6" style="background: linear-gradient(135deg,#1b3c2c,#0d2118);">
        <div class="px-6 py-5 sm:px-8 sm:py-6 flex flex-wrap items-start justify-between gap-5">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-widest text-gold-400 font-semibold">Portal Access</p>
                <h2 class="font-display text-2xl sm:text-3xl font-bold text-white mt-1 truncate">{{ $manager->name }}</h2>
                <p class="text-sm text-brand-200 mt-1 truncate">{{ $manager->email }}</p>
            </div>

            <div class="text-right">
                <p class="text-xs uppercase tracking-wider text-brand-300">Granted</p>
                <p class="font-display text-3xl font-bold text-gold-400 mt-0.5">
                    <span data-permission-count>{{ $grantedCount }}</span><span class="text-brand-300 text-xl">/{{ $total }}</span>
                </p>
            </div>
        </div>

        <p class="px-6 sm:px-8 py-3 border-t border-white/10 text-xs text-brand-200">
            Set by Super Admin only, and never passed down to their Commission Partners.
            Modules without a live page are not listed.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.branch-managers.permissions.update', $manager) }}"
          data-permission-matrix class="max-w-3xl">
        @csrf
        @method('PUT')

        {{-- Whole-grid toggle. Rows and columns have their own, below. --}}
        <div class="bg-white rounded-xl border border-slate-200 px-5 py-4 mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold text-slate-800">Everything</p>
                <p class="text-xs text-slate-500">Turn every permission below on or off at once.</p>
            </div>
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" data-permission-all class="peer sr-only">
                <span class="px-4 py-2 rounded-full text-sm font-semibold border transition
                             border-slate-300 text-slate-600
                             peer-checked:bg-brand-700 peer-checked:border-brand-700 peer-checked:text-white
                             peer-indeterminate:bg-brand-50 peer-indeterminate:border-brand-300 peer-indeterminate:text-brand-700">
                    Select all
                </span>
            </label>
        </div>

        {{-- Per-action toggles: one action across every module. --}}
        @if (count($modules) > 1)
            <div class="bg-white rounded-xl border border-slate-200 px-5 py-4 mb-4">
                <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Or by action, across all modules</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($actions as $action)
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" data-permission-column="{{ $action }}" class="peer sr-only">
                            <span class="capitalize px-3.5 py-1.5 rounded-full text-sm font-medium border transition
                                         border-slate-300 text-slate-600
                                         peer-checked:bg-brand-700 peer-checked:border-brand-700 peer-checked:text-white
                                         peer-indeterminate:bg-brand-50 peer-indeterminate:border-brand-300 peer-indeterminate:text-brand-700">
                                {{ $action }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Kept for the single-module case so the column toggles still exist. --}}
            <div class="hidden">
                @foreach ($actions as $action)
                    <input type="checkbox" data-permission-column="{{ $action }}">
                @endforeach
            </div>
        @endif

        {{-- One card per module. --}}
        <div class="space-y-4">
            @forelse ($modules as $module)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                                <x-icon name="{{ $moduleIcons[$module] ?? 'tag' }}" class="w-4 h-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 capitalize">{{ str_replace('-', ' ', $module) }}</p>
                                <p class="text-xs text-slate-500">Their portal page for this area</p>
                            </div>
                        </div>

                        {{-- Row toggle: every action for this module. --}}
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" data-permission-row="{{ $module }}" class="peer sr-only"
                                   aria-label="All permissions for {{ $module }}">
                            <span class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition
                                         border-slate-300 text-slate-600
                                         peer-checked:bg-brand-700 peer-checked:border-brand-700 peer-checked:text-white
                                         peer-indeterminate:bg-brand-50 peer-indeterminate:border-brand-300 peer-indeterminate:text-brand-700">
                                Full access
                            </span>
                        </label>
                    </div>

                    {{-- One row per action, each with its own on/off switch. The
                         whole row is the label, so anywhere on it toggles. --}}
                    <div class="divide-y divide-slate-100">
                        @foreach ($actions as $action)
                            @php $permission = "branch.{$module}.{$action}"; @endphp
                            <label class="block cursor-pointer select-none">
                                <input type="checkbox" name="permissions[{{ $permission }}]" value="1"
                                       data-permission-box data-action="{{ $action }}" data-module="{{ $module }}"
                                       class="perm-check sr-only"
                                       aria-label="{{ $action }} {{ $module }}"
                                       @checked(in_array($permission, $granted, true))>

                                <span class="perm-row flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-slate-50">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-slate-800 capitalize text-sm">{{ $action }}</span>
                                        <span class="block text-xs text-slate-500 mt-0.5">
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
                <div class="bg-white rounded-xl border border-slate-200 px-5 py-10 text-center">
                    <p class="text-slate-500">No modules are live yet, so there is nothing to grant.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-md hover:bg-brand-800 transition">
                Save Permissions
            </button>
            <a href="{{ route('admin.branch-managers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
</x-layouts.app>
