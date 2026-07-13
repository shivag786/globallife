<x-layouts.app title="Branch Manager Permissions" heading="Permissions — {{ $manager->name }}">
    <p class="text-sm text-slate-500 mb-6">
        Controls which pages {{ $manager->name }} can see in their portal and what they can do there.
        These permissions are set by Super Admin only and are not passed down to their Commission Partners.
    </p>

    <form method="POST" action="{{ route('admin.branch-managers.permissions.update', $manager) }}" class="bootstrap-scope max-w-2xl">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            @foreach ($actions as $action)
                                <th class="text-capitalize">{{ $action }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $module)
                            <tr>
                                <td class="text-capitalize">{{ str_replace('-', ' ', $module) }}</td>
                                @foreach ($actions as $action)
                                    @php $permission = "branch.{$module}.{$action}"; @endphp
                                    <td>
                                        <input type="checkbox" name="permissions[{{ $permission }}]" value="1"
                                               @checked(in_array($permission, $granted, true))>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                Save Permissions
            </button>
        </div>
    </form>
</x-layouts.app>
