<x-layouts.app title="Departments">
    <x-ui.page-header title="Departments" subtitle="Organizational departments">
        <x-slot:actions>
            @can('create', App\Models\Department::class)
                <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">+ New Department</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="GET" class="flex gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="input input-bordered input-sm w-full max-w-xs" />
                <button class="btn btn-sm">Search</button>
            </form>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Positions</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr>
                                <td class="font-mono text-sm">{{ $department->code }}</td>
                                <td class="font-medium">{{ $department->name }}</td>
                                <td>{{ $department->positions_count ?? $department->positions()->count() }}</td>
                                <td class="text-right">
                                    @can('update', $department)
                                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $department)
                                        <form method="POST" action="{{ route('departments.destroy', $department) }}" class="inline" onsubmit="return confirm('Delete this department?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-base-content/50 py-6">No departments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $departments->links() }}</div>
        </div>
    </div>
</x-layouts.app>
