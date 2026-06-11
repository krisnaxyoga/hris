<x-layouts.app title="Companies">
    <x-ui.page-header title="Companies" subtitle="Manage tenant companies">
        <x-slot:actions>
            @can('create', App\Models\Company::class)
                <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">+ New Company</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="GET" class="flex gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search companies…" class="input input-bordered input-sm w-full max-w-xs" />
                <button class="btn btn-sm">Search</button>
            </form>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td class="font-medium">{{ $company->name }}</td>
                                <td>{{ $company->email ?? '—' }}</td>
                                <td><span class="badge badge-ghost">{{ ucfirst($company->subscription_plan) }}</span></td>
                                <td>
                                    <span class="badge {{ $company->is_active ? 'badge-success' : 'badge-error' }}">
                                        {{ $company->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @can('update', $company)
                                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $company)
                                        <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline" onsubmit="return confirm('Delete this company?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-base-content/50 py-6">No companies found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $companies->links() }}</div>
        </div>
    </div>
</x-layouts.app>
