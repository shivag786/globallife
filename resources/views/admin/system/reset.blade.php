<x-layouts.app title="Erase All Data" heading="Erase All Data">
    <div class="alert alert-danger d-flex gap-3 align-items-start">
        <x-icon name="lock" class="w-6 h-6 flex-shrink-0 mt-1" />
        <div>
            <strong>Danger zone.</strong> This deletes all business data so the platform starts fresh. It cannot be undone
            from here; a full database backup is taken first and can be downloaded below.
        </div>
    </div>

    @if ($last)
        <p class="small text-secondary">
            Last erased <x-ist :value="$last['at']" /> by {{ $last['by'] }} (backup {{ $last['backup'] }}).
        </p>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-danger mb-4">
                <div class="card-header bg-danger text-white fw-semibold">What will be deleted</div>
                <div class="card-body">
                    <table class="w-100 small mb-3">
                        @foreach ($summary as $label => $count)
                            <tr class="border-bottom">
                                <td class="py-1">{{ $label }}</td>
                                <td class="py-1 text-end fw-semibold">{{ number_format($count) }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <p class="small mb-2">
                        Also cleared: saved addresses, VIP page content (banners, gallery, services, reviews, videos), order payments
                        and settlements, salary advances, assets, expense claims, HR announcements, the activity log, and every other
                        login session.
                    </p>
                    <p class="small mb-0">
                        Uploaded files that only belonged to deleted data (KYC scans, VIP page photos, HR documents, receipts) are
                        <strong>moved into the backup folder</strong>, not destroyed.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm border-success">
                <div class="card-header bg-success-subtle fw-semibold">What stays</div>
                <div class="card-body small">
                    Super Admin accounts (and your login), roles &amp; permissions, site settings and logo, products, categories,
                    brands, cities, VIP plans, product commission rules, homepage sections, blog posts, events, testimonials,
                    media library, and the HR setup (departments, designations, shifts, holidays, leave types).
                    The database structure is never changed.
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold d-flex align-items-center gap-2">
                    <x-icon name="lock" class="w-4 h-4" /> Step 1 &middot; Unlock
                </div>
                <div class="card-body">
                    @if (! $enabled)
                        <p class="small mb-0">
                            This feature is <strong>switched off</strong>. To turn it on, add a reset password (at least 8 characters)
                            to the server's <code>.env</code> file:
                        </p>
                        <pre class="small bg-body-tertiary border rounded p-2 mt-2 mb-0">DATA_RESET_PASSWORD=your-secret-password</pre>
                        <p class="small text-secondary mt-2 mb-0">Then run <code>php artisan config:clear</code>.</p>
                    @elseif ($unlocked)
                        <p class="small text-success mb-2">
                            Unlocked until <x-ist :value="\Illuminate\Support\Carbon::createFromTimestamp($unlockedUntil)" format="h:i A" />.
                        </p>
                        <form method="POST" action="{{ route('admin.system.reset.lock') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Lock again</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.system.reset.unlock') }}" class="d-flex flex-column gap-2">
                            @csrf
                            <label class="form-label small fw-medium mb-0" for="reset_password">Reset password</label>
                            <input type="password" name="reset_password" id="reset_password" autocomplete="off" required
                                   class="form-control @error('reset_password') is-invalid @enderror">
                            @error('reset_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <div class="form-text">This is the DATA_RESET_PASSWORD from the server, not your login password.</div>
                            <button class="btn btn-dark align-self-start">Unlock</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-danger {{ $unlocked ? '' : 'opacity-50' }}">
                <div class="card-header fw-semibold d-flex align-items-center gap-2 text-danger">
                    <x-icon name="trash" class="w-4 h-4" /> Step 2 &middot; Erase
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system.reset.erase') }}" class="d-flex flex-column gap-2"
                          onsubmit="return confirm('Erase ALL business data now? A backup is taken first, but this cannot be undone from the panel.')">
                        @csrf @method('DELETE')
                        <label class="form-label small fw-medium mb-0" for="confirmation">
                            Type <code>{{ $phrase }}</code> to confirm
                        </label>
                        <input type="text" name="confirmation" id="confirmation" autocomplete="off" @disabled(! $unlocked)
                               class="form-control @error('confirmation') is-invalid @enderror" data-erase-phrase="{{ $phrase }}">
                        @error('confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <button class="btn btn-danger fw-semibold" data-erase-button disabled>
                            Back up &amp; erase all business data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header fw-semibold">Backups</div>
        <div class="card-body p-0">
            <table class="w-100 small">
                <thead class="bg-body-tertiary text-secondary">
                    <tr><th class="px-3 py-2">Backup</th><th class="px-3 py-2">Taken</th><th class="px-3 py-2">Database</th><th class="px-3 py-2">Moved files</th><th class="px-3 py-2"></th></tr>
                </thead>
                <tbody>
                    @forelse ($backups as $b)
                        <tr class="border-top">
                            <td class="px-3 py-2 font-monospace">{{ $b['name'] }}</td>
                            <td class="px-3 py-2"><x-ist :value="\Illuminate\Support\Carbon::createFromTimestamp($b['at'])" /></td>
                            <td class="px-3 py-2">{{ number_format($b['size'] / 1024, 1) }} KB</td>
                            <td class="px-3 py-2">{{ $b['files'] }}</td>
                            <td class="px-3 py-2 text-end text-nowrap">
                                <a href="{{ route('admin.system.backups.download', $b['name']) }}" class="me-3">Download SQL</a>
                                <form method="POST" action="{{ route('admin.system.backups.destroy', $b['name']) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this backup permanently (database file and moved files)?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link btn-sm text-danger p-0">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-3 text-secondary">No backups yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-secondary">
            Backups are stored privately on the server (<code>storage/app/private/backups</code>). To restore, import the SQL file in
            phpMyAdmin and copy the moved files back from the backup's <code>files</code> folder.
        </div>
    </div>

    <script>
        (function () {
            const input = document.querySelector('[data-erase-phrase]');
            const button = document.querySelector('[data-erase-button]');
            if (!input || !button) return;
            const sync = () => { button.disabled = input.disabled || input.value !== input.dataset.erasePhrase; };
            input.addEventListener('input', sync);
            sync();
        })();
    </script>
</x-layouts.app>
