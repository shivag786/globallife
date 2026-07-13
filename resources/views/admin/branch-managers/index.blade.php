<x-layouts.app title="Branch Managers" heading="Branch Managers">
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.branch-managers.create') }}" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add Branch Manager
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Branches (Cities)</th>
                    <th class="px-4 py-3">Commission Cap</th>
                    <th class="px-4 py-3">Commission Partners</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($managers as $manager)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $manager->name }}</td>
                        <td class="px-4 py-3">{{ $manager->email }}</td>
                        <td class="px-4 py-3">{{ $manager->branchCities->pluck('name')->implode(', ') }}</td>
                        <td class="px-4 py-3">{{ $manager->commission_percentage }}%</td>
                        <td class="px-4 py-3">{{ $manager->commission_partners_count }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $manager->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ $manager->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.branch-managers.permissions.edit', $manager) }}" class="text-indigo-600 hover:underline">Permissions</a>
                            <a href="{{ route('admin.branch-managers.edit', $manager) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.branch-managers.toggle-status', $manager) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-amber-600 hover:underline">
                                    {{ $manager->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No branch managers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
