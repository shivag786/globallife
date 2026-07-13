<x-layouts.app title="Services" heading="Services">
    <div class="flex justify-end mb-4">
        <a href="{{ route('vip.services.create') }}" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add Service
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $service->name }} @if($service->is_featured) <span class="text-xs bg-gold-400/20 text-gold-600 px-2 py-0.5 rounded">Featured</span> @endif</td>
                        <td class="px-4 py-3">{{ $service->category ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $service->offer_price ?? $service->mrp ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $service->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $service->sort_order }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('vip.services.edit', $service) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('vip.services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No services yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
