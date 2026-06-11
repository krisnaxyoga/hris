<x-layouts.app title="Employees">
    <x-ui.page-header title="Employees" subtitle="Manage your workforce">
        <x-slot:actions>
            @can('create', App\Models\EmployeeProfile::class)
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">+ New Employee</a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, code, NIK…" class="input input-bordered input-sm w-full max-w-xs" />
                <select name="department_id" class="select select-bordered select-sm">
                    <option value="">All departments</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(request('department_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="employment_status" class="select select-bordered select-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('employment_status') === $value)>{{ $label }}</option>
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
                            <th>Position</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="font-mono text-sm">{{ $employee->employee_code }}</td>
                                <td class="font-medium">
                                    <a href="{{ route('employees.show', $employee) }}" class="link link-hover">{{ $employee->full_name }}</a>
                                </td>
                                <td>{{ $employee->department?->name ?? '—' }}</td>
                                <td>{{ $employee->position?->name ?? '—' }}</td>
                                <td>{{ $employee->manager?->full_name ?? '—' }}</td>
                                <td><span class="badge {{ $employee->employment_status->color() }}">{{ $employee->employment_status->label() }}</span></td>
                                <td>{{ $employee->join_date?->format('d M Y') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost btn-xs">View</a>
                                    @can('update', $employee)
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    @endcan
                                    @can('delete', $employee)
                                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="inline" onsubmit="return confirm('Delete this employee?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-base-content/50 py-6">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $employees->links() }}</div>
        </div>
    </div>
</x-layouts.app>
