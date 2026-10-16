<x-layouts.app title="Lead Detail" heading="Lead Detail">
    <div class="d-grid grid-cols-md-3 gap-6 mw-4xl">
        <div class="col-span-md-2 bg-body rounded-2 shadow-sm border p-6 d-flex flex-column gap-4">
            <div>
                <p class="fs-xs text-secondary text-uppercase tracking-wide">Name</p>
                <p class="fw-medium">{{ $lead->name }}</p>
            </div>
            <div class="d-grid grid-cols-2 gap-4">
                <div>
                    <p class="fs-xs text-secondary text-uppercase tracking-wide">Email</p>
                    <p>{{ $lead->email }}</p>
                </div>
                <div>
                    <p class="fs-xs text-secondary text-uppercase tracking-wide">Phone</p>
                    <p>{{ $lead->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="fs-xs text-secondary text-uppercase tracking-wide">City</p>
                    <p>{{ $lead->city ?? '—' }}</p>
                </div>
                <div>
                    <p class="fs-xs text-secondary text-uppercase tracking-wide">Source</p>
                    <p class="text-capitalize">{{ str_replace('_', ' ', $lead->source) }}</p>
                </div>
                {{-- Recorded from the product page the enquiry was sent on. If the
                     message names a different product, this is the one to trust. --}}
                @if ($lead->product)
                    <div>
                        <p class="fs-xs text-secondary text-uppercase tracking-wide">Product</p>
                        <p>
                            <a href="{{ route('products.show', $lead->product) }}" target="_blank"
                               class="text-brand hover-underline">{{ $lead->product->name }}</a>
                        </p>
                    </div>
                @endif
                @if ($lead->interestedPlan)
                    <div>
                        <p class="fs-xs text-secondary text-uppercase tracking-wide">Interested Plan</p>
                        <p>{{ $lead->interestedPlan->name }}</p>
                    </div>
                @endif
            </div>
            @if ($lead->message)
                <div>
                    <p class="fs-xs text-secondary text-uppercase tracking-wide">Message</p>
                    <p class="">{{ $lead->message }}</p>
                </div>
            @endif
            <p class="fs-xs text-secondary">Submitted {{ $lead->created_at->format('M j, Y g:i A') }}</p>
        </div>

        <div class="bg-body rounded-2 shadow-sm border p-6">
            <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="d-flex flex-column gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
                    <select id="status" name="status" class="mt-1 d-block w-100 rounded-2">
                        @foreach (['new', 'contacted', 'converted', 'closed'] as $option)
                            <option value="{{ $option }}" @selected(old('status', $lead->status) === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned_manager_id" class="d-block fs-sm fw-medium text-body-secondary">Assigned Manager</label>
                    <select id="assigned_manager_id" name="assigned_manager_id" class="mt-1 d-block w-100 rounded-2">
                        <option value="">Unassigned</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('assigned_manager_id', $lead->assigned_manager_id) == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100">
                    Save
                </button>
            </form>

            @can('leads.delete')
                <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" class="mt-3" data-confirm="Delete this lead?" data-confirm-danger>
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 text-decoration-none w-100 text-danger fs-sm hover-underline">Delete Lead</button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts.app>
