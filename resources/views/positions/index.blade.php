<x-layouts.app title="Positions">
    <x-ui.page-header title="Positions" subtitle="Job positions per department">
        <x-slot:actions>
            @can('create', App\Models\Position::class)
                <a href="{{ route('positions.create') }}" class="btn btn-primary btn-sm">+ New Position</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="input input-bordered input-sm w-full max-w-xs" />
                <select name="department_id" class="select select-bordered select-sm">
                    <option value="">All departments</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(request('department_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            <tr>
                                <td class="font-mono text-sm">{{ $position->code }}</td>
                                <td class="font-medium">{{ $position->name }}</td>
                                <td>{{ $position->department?->name ?? '—' }}</td>
                                <td class="text-right">
                                    @can('update', $position)
                                        <a href="{{ route('positions.edit', $position) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $position)
                                        <form method="POST" action="{{ route('positions.destroy', $position) }}" class="inline" onsubmit="return confirm('Delete this position?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-base-content/50 py-6">No positions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $positions->links() }}</div>
        </div>
    </div>
</x-layouts.app>
