<x-layouts.app title="Lead Detail" heading="Lead Detail">
    <div class="grid md:grid-cols-3 gap-6 max-w-4xl">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-slate-100 p-6 space-y-4">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Name</p>
                <p class="font-medium">{{ $lead->name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Email</p>
                    <p>{{ $lead->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Phone</p>
                    <p>{{ $lead->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">City</p>
                    <p>{{ $lead->city ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Source</p>
                    <p class="capitalize">{{ str_replace('_', ' ', $lead->source) }}</p>
                </div>
                @if ($lead->interestedPlan)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Interested Plan</p>
                        <p>{{ $lead->interestedPlan->name }}</p>
                    </div>
                @endif
            </div>
            @if ($lead->message)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Message</p>
                    <p class="whitespace-pre-line">{{ $lead->message }}</p>
                </div>
            @endif
            <p class="text-xs text-slate-400">Submitted {{ $lead->created_at->format('M j, Y g:i A') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['new', 'contacted', 'converted', 'closed'] as $option)
                            <option value="{{ $option }}" @selected(old('status', $lead->status) === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned_manager_id" class="block text-sm font-medium text-slate-700">Assigned Manager</label>
                    <select id="assigned_manager_id" name="assigned_manager_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Unassigned</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('assigned_manager_id', $lead->assigned_manager_id) == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                    Save
                </button>
            </form>

            @can('leads.delete')
                <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this lead?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-red-600 text-sm hover:underline">Delete Lead</button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts.app>
