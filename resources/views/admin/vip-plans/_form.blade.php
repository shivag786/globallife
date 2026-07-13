@csrf
@isset($plan) @method('PUT') @endisset

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Plan Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @foreach (['monthly_price' => 'Monthly Price', 'yearly_price' => 'Yearly Price', 'joining_price' => 'Joining Price', 'renewal_price' => 'Renewal Price'] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                <input id="{{ $field }}" type="number" step="0.01" min="0" name="{{ $field }}"
                       value="{{ old($field, $plan->{$field} ?? 0) }}" required
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endforeach
    </div>

    <div>
        <label for="features" class="block text-sm font-medium text-slate-700">Features (one per line)</label>
        <textarea id="features" name="features" rows="4"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('features', isset($plan) ? implode("\n", $plan->features ?? []) : '') }}</textarea>
    </div>

    <div>
        <label for="highlight_features" class="block text-sm font-medium text-slate-700">Highlight Features (one per line)</label>
        <textarea id="highlight_features" name="highlight_features" rows="2"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('highlight_features', isset($plan) ? implode("\n", $plan->highlight_features ?? []) : '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ([
            'microsite_limit' => 'Microsite Limit',
            'landing_page_limit' => 'Landing Page Limit',
            'blog_limit' => 'Blog Limit',
            'analytics_limit_days' => 'Analytics Limit (days)',
            'storage_limit_mb' => 'Storage Limit (MB)',
            'upgrade_priority' => 'Upgrade Priority',
            'display_order' => 'Display Order',
        ] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                <input id="{{ $field }}" type="number" min="0" name="{{ $field }}"
                       value="{{ old($field, $plan->{$field} ?? 0) }}" required
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        @endforeach

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $plan->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($plan) ? 'Update Plan' : 'Create Plan' }}
    </button>
</div>
