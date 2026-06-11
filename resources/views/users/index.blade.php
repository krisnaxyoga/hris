<x-layouts.app title="Users">
    <x-ui.page-header title="Users" subtitle="System user accounts & roles">
        <x-slot:actions>
            @can('create', App\Models\User::class)
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ New User</a>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="font-medium">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach ($user->getRoleNames() as $role)
                                        <span class="badge badge-outline badge-sm">{{ $role }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-error' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @can('update', $user)
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-base-content/50 py-6">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-layouts.app>
