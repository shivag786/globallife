<x-layouts.app title="Brands" heading="Brands">
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.brands.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
            + Add Brand
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($brand->logo)
                                    <img src="{{ asset('storage/'.$brand->logo) }}" alt="" class="w-9 h-9 rounded object-contain bg-slate-50">
                                @else
                                    <span class="w-9 h-9 rounded bg-slate-100 flex items-center justify-center text-slate-400 text-xs">—</span>
                                @endif
                                <span class="font-medium">{{ $brand->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $brand->products_count }}</td>
                        <td class="px-4 py-3">{{ $brand->display_order }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $brand->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $brand->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline" data-confirm="Delete this brand?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No brands yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
