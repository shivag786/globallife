{{-- Unseen leads at the top of the admin dashboard, in red. A lead leaves this
     list (and the sidebar count) once someone opens it. --}}
<div class="card shadow-sm mb-6 border-danger border-2 overflow-hidden" id="new-leads">
    <div class="card-header bg-danger text-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <x-icon name="inbox" class="w-5 h-5" />
            <h2 class="h6 fw-bold mb-0">New Leads</h2>
            <span class="badge rounded-pill bg-white text-danger">{{ $count }} new</span>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-light text-danger fw-semibold">View all leads</a>
    </div>
    <div class="card-body p-0">
        @foreach ($leads as $lead)
            <a href="{{ route('admin.leads.show', $lead) }}"
               class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-3 text-decoration-none bg-danger-subtle text-danger-emphasis {{ $loop->first ? '' : 'border-top border-danger-subtle' }}">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span class="badge bg-danger">NEW</span>
                    <strong class="text-truncate">{{ $lead->name }}</strong>
                    <span class="small text-truncate">
                        {{ $lead->phone ?: $lead->email }}{{ $lead->city ? ' · '.$lead->city : '' }}
                    </span>
                </div>
                <div class="small text-nowrap">
                    <span class="text-capitalize">{{ str_replace('_', ' ', $lead->source) }}</span>
                    &middot; <x-ist :value="$lead->created_at" format="d M, h:i A" />
                </div>
            </a>
        @endforeach
        @if ($count > $leads->count())
            <p class="small text-danger-emphasis px-4 py-2 mb-0 border-top">+ {{ $count - $leads->count() }} more on the Leads page.</p>
        @endif
    </div>
</div>
