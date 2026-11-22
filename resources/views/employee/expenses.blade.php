<x-layouts.app title="Expense Claims" heading="Expense Claims">
    <div class="d-grid grid-cols-1 grid-cols-md-3 gap-4 mb-4">
        <x-stat-tile label="Waiting for approval" :value="$totals['pending']" money icon="inbox" color="amber" />
        <x-stat-tile label="Approved, to be paid" :value="$totals['approved']" money icon="check-circle" color="sky" />
        <x-stat-tile label="Reimbursed" :value="$totals['paid']" money icon="rupee" color="emerald" />
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">New claim</div>
                <form method="POST" action="{{ route('employee.expenses.store') }}" enctype="multipart/form-data" class="card-body d-flex flex-column gap-3">
                    @csrf
                    <input name="title" value="{{ old('title') }}" class="form-control" placeholder="What was it for?" required>
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="category" class="form-select" required>
                                @foreach ($categories as $c) <option @selected(old('category') === $c)>{{ $c }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6"><input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="Amount" required></div>
                    </div>
                    <input type="date" name="expense_date" value="{{ old('expense_date', \App\Support\HrClock::today()->format('Y-m-d')) }}" max="{{ \App\Support\HrClock::today()->format('Y-m-d') }}" class="form-control" required>
                    <textarea name="description" rows="2" class="form-control" placeholder="Details (optional)">{{ old('description') }}</textarea>
                    <div>
                        <label class="form-label small fw-medium">Bill / receipt</label>
                        <input type="file" name="receipt" accept=".pdf,image/*" class="form-control @error('receipt') is-invalid @enderror">
                        @error('receipt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button class="btn btn-brand">Submit claim</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-100 fs-sm">
                        <thead class="bg-body-tertiary text-secondary">
                            <tr><th class="px-3 py-3">Claim</th><th class="px-3 py-3 text-end">Amount</th><th class="px-3 py-3">Receipt</th><th class="px-3 py-3">Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($claims as $c)
                                <tr class="border-top align-top">
                                    <td class="px-3 py-2"><strong>{{ $c->title }}</strong><div class="fs-xs text-secondary">{{ $c->category }} &middot; {{ $c->expense_date->format('d M Y') }}</div></td>
                                    <td class="px-3 py-2 text-end">{{ number_format($c->amount, 2) }}</td>
                                    <td class="px-3 py-2">@if ($c->receipt_path) <a href="{{ route('hr.files.receipt', $c) }}" target="_blank">View</a> @else - @endif</td>
                                    <td class="px-3 py-2">
                                        @include('hr.partials.badge', ['status' => $c->status])
                                        @if ($c->review_note) <div class="fs-xs text-secondary">{{ $c->review_note }}</div> @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-secondary">No claims yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $claims->links() }}</div>
        </div>
    </div>
</x-layouts.app>
