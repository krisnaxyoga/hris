<x-layouts.app title="Timesheets">
    <x-ui.page-header title="Timesheets" subtitle="Log time against projects and tasks" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @if ($employee)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg">New Entry</h2>
                    <form method="POST" action="{{ route('timesheets.store') }}">
                        @csrf
                        <x-form.input label="Work Date" name="work_date" type="date" required />
                        <x-form.input label="Project" name="project_name" required />
                        <x-form.input label="Task" name="task_name" required />
                        <x-form.input label="Hours Spent" name="hours_spent" type="number" step="0.25" required />
                        <x-form.textarea label="Notes" name="notes" />
                        <button type="submit" class="btn btn-primary w-full mt-4">Save Draft</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card bg-base-100 shadow lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title text-lg">My Timesheets</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr><th>Date</th><th>Project</th><th>Task</th><th>Hours</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($mine ?? [] as $row)
                                <tr>
                                    <td>{{ $row->work_date->format('d M Y') }}</td>
                                    <td>{{ $row->project_name }}</td>
                                    <td>{{ $row->task_name }}</td>
                                    <td>{{ $row->hours_spent }}</td>
                                    <td><span class="badge {{ $row->status->color() }}">{{ $row->status->label() }}</span></td>
                                    <td class="text-right">
                                        @if ($row->status === \App\Enums\TimesheetStatus::Draft)
                                            <form method="POST" action="{{ route('timesheets.submit', $row) }}">
                                                @csrf
                                                <button class="btn btn-primary btn-xs">Submit</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-base-content/50 py-6">No timesheets yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($mine)<div class="mt-4">{{ $mine->links() }}</div>@endif
            </div>
        </div>
    </div>

    @if ($review->isNotEmpty())
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg">Pending Review</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr><th>Employee</th><th>Date</th><th>Project</th><th>Hours</th><th class="text-right">Decision</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($review as $row)
                                <tr>
                                    <td>{{ $row->employee->full_name }}</td>
                                    <td>{{ $row->work_date->format('d M Y') }}</td>
                                    <td>{{ $row->project_name }} — {{ $row->task_name }}</td>
                                    <td>{{ $row->hours_spent }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-2 justify-end">
                                            <form method="POST" action="{{ route('timesheets.approve', $row) }}">
                                                @csrf
                                                <button class="btn btn-success btn-xs">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('timesheets.reject', $row) }}">
                                                @csrf
                                                <button class="btn btn-error btn-xs">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
