{{--
    One "Super Admin updated this Commission Partner" note.

    @param \App\Models\CommissionPartnerUpdate $update
    @param bool $showPartner  name the partner (dashboard) or not (their own page)
    @param bool $canMarkRead  show the Branch Manager's "Mark as read" button
    @param string $unreadLabel  badge text while unread ("New" for the Branch
                                Manager; Super Admin sees "Not seen yet")
--}}
@php
    $showPartner = $showPartner ?? true;
    $canMarkRead = $canMarkRead ?? false;
    $unreadLabel = $unreadLabel ?? 'New';
@endphp

<div class="d-flex align-items-start gap-3 py-3 {{ $update->isRead() ? '' : 'bg-gold-subtle rounded-3 px-3' }}">
    <span class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle bg-brand-subtle text-brand w-9 h-9 mt-1">
        <x-icon name="sparkles" class="w-4 h-4" />
    </span>

    <div class="flex-grow-1 min-w-0">
        <p class="fs-sm mb-1">
            <strong>{{ $update->updatedBy?->name ?? 'Super Admin' }}</strong>
            updated
            @if ($showPartner)
                <strong>{{ $update->partner?->name ?? 'a Commission Partner' }}</strong>
            @else
                this partner
            @endif
            @unless ($update->isRead())
                <span class="badge rounded-pill text-bg-warning ms-1">{{ $unreadLabel }}</span>
            @endunless
        </p>

        @if ($update->changed_fields)
            <ul class="list-unstyled fs-sm mb-1">
                @foreach ($update->describedChanges() as $change)
                    <li>
                        <span class="text-secondary">{{ $change['label'] }}:</span>
                        <span class="text-decoration-line-through text-secondary">{{ $change['old'] }}</span>
                        <span class="mx-1">&rarr;</span>
                        <strong>{{ $change['new'] }}</strong>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($update->note)
            <p class="fs-sm fst-italic text-body-secondary mb-1">&ldquo;{{ $update->note }}&rdquo;</p>
        @endif

        <p class="fs-xs text-secondary mb-0">
            {{ $update->created_at->format('d M Y, g:i A') }}
            &middot; {{ $update->created_at->diffForHumans() }}
        </p>
    </div>

    @if ($canMarkRead && ! $update->isRead())
        <form method="POST" action="{{ route('branch.partner-updates.read') }}" class="flex-shrink-0">
            @csrf
            @method('PATCH')
            <input type="hidden" name="id" value="{{ $update->id }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark as read</button>
        </form>
    @endif
</div>
