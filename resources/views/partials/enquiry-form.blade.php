@php
    $source = $source ?? 'contact_page';
    $plans = $plans ?? collect();
    $preselectedPlan = $preselectedPlan ?? null;
    $vipMicrositeId = $vipMicrositeId ?? null;
    $prefillCity = $prefillCity ?? null;
    $prefillMessage = $prefillMessage ?? null;
@endphp

@if (session('status'))
    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('enquiry.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    @if ($vipMicrositeId)
        <input type="hidden" name="vip_microsite_id" value="{{ $vipMicrositeId }}">
    @endif
    <div class="hidden" aria-hidden="true">
        <label for="website-{{ $source }}">Website</label>
        <input type="text" id="website-{{ $source }}" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label for="name-{{ $source }}" class="block text-sm font-medium text-slate-700">Name</label>
            <input id="name-{{ $source }}" type="text" name="name" value="{{ old('name') }}" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="email-{{ $source }}" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email-{{ $source }}" type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="phone-{{ $source }}" class="block text-sm font-medium text-slate-700">Phone</label>
            <input id="phone-{{ $source }}" type="text" name="phone" value="{{ old('phone') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="city-{{ $source }}" class="block text-sm font-medium text-slate-700">City</label>
            <input id="city-{{ $source }}" type="text" name="city" value="{{ old('city', $prefillCity) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
    </div>

    @if ($plans->isNotEmpty())
        <div>
            <label for="interested_plan_id-{{ $source }}" class="block text-sm font-medium text-slate-700">Interested Plan <span class="text-slate-400">(optional)</span></label>
            <select id="interested_plan_id-{{ $source }}" name="interested_plan_id"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">None / General Enquiry</option>
                @foreach ($plans as $plan)
                    @php
                        $isSelected = old('interested_plan_id')
                            ? (string) old('interested_plan_id') === (string) $plan->id
                            : $preselectedPlan === $plan->slug;
                    @endphp
                    <option value="{{ $plan->id }}" @selected($isSelected)>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <label for="message-{{ $source }}" class="block text-sm font-medium text-slate-700">Message</label>
        <textarea id="message-{{ $source }}" name="message" rows="4"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('message', $prefillMessage) }}</textarea>
    </div>

    <button type="submit" class="bg-brand-700 text-white px-6 py-3 rounded-full font-medium hover:bg-brand-800 transition">
        Send Enquiry
    </button>
</form>
