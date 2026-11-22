@php
    $tones = [
        'active' => 'success', 'approved' => 'success', 'present' => 'success', 'paid' => 'success', 'finalized' => 'success', 'available' => 'success',
        'pending' => 'warning', 'draft' => 'warning', 'on_notice' => 'warning', 'half_day' => 'warning', 'repair' => 'warning', 'late' => 'warning',
        'rejected' => 'danger', 'absent' => 'danger', 'terminated' => 'danger', 'unpaid_leave' => 'danger',
        'cancelled' => 'secondary', 'resigned' => 'secondary', 'retired' => 'secondary', 'closed' => 'secondary', 'weekly_off' => 'secondary',
        'leave' => 'info', 'holiday' => 'info', 'assigned' => 'primary',
    ];
    $tone = $tones[$status] ?? 'secondary';
@endphp
<span class="badge rounded-pill bg-{{ $tone }}-subtle text-{{ $tone }}-emphasis border border-{{ $tone }}-subtle fw-medium">
    {{ $label ?? ucwords(str_replace('_', ' ', $status)) }}
</span>
