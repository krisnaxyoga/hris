<x-layouts.app title="Attendance Zones">
    <x-ui.page-header title="Attendance Zones" subtitle="Office geofences used for GPS check-in">
        <x-slot:actions>
            @can('create', App\Models\AttendanceLocation::class)
                <a href="{{ route('attendance-locations.create') }}" class="btn btn-primary btn-sm">+ New Zone</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Coordinates</th>
                            <th>Radius</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                            <tr>
                                <td class="font-medium">{{ $location->name }}</td>
                                <td class="font-mono text-xs">{{ $location->latitude }}, {{ $location->longitude }}</td>
                                <td><span class="badge badge-ghost">{{ number_format($location->radius_meter) }} m</span></td>
                                <td>
                                    <span class="badge {{ $location->is_active ? 'badge-success' : 'badge-error' }}">
                                        {{ $location->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @can('update', $location)
                                        <a href="{{ route('attendance-locations.edit', $location) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $location)
                                        <form method="POST" action="{{ route('attendance-locations.destroy', $location) }}" class="inline" onsubmit="return confirm('Delete this zone?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-base-content/50 py-6">No attendance zones yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $locations->links() }}</div>
        </div>
    </div>
</x-layouts.app>
