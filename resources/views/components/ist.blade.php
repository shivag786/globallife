@props(['value', 'format' => 'd M Y, g:i A', 'suffix' => ' IST'])
{{-- Renders a stored (UTC) timestamp in India Standard Time. --}}
@if (! is_null($value))
    {{ \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Kolkata')->format($format) }}{{ $suffix }}
@endif
