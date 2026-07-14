<x-layouts.app title="VIP Plans" heading="VIP Plans">
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.vip-plans.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
            + Add VIP Plan
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Monthly</th>
                    <th class="px-4 py-3">Yearly</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $plan->display_order }}</td>
                        <td class="px-4 py-3 font-medium">{{ $plan->name }}</td>
                        <td class="px-4 py-3">₹{{ number_format($plan->monthly_price, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($plan->yearly_price, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $plan->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $plan->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.vip-plans.edit', $plan) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.vip-plans.destroy', $plan) }}" method="POST" class="inline" data-confirm="Delete this plan?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No VIP plans yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
