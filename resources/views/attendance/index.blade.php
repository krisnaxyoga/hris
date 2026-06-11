<x-layouts.app title="Attendance Log">
    <x-ui.page-header title="Attendance Log" subtitle="Company-wide attendance & reports" />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['label' => 'Present', 'value' => $summary['present'], 'class' => 'text-success'],
                ['label' => 'Late', 'value' => $summary['late'], 'class' => 'text-warning'],
                ['label' => 'Absent', 'value' => $summary['absent'], 'class' => 'text-error'],
                ['label' => 'Total Hours', 'value' => $summary['total_hours'], 'class' => ''],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <p class="text-sm text-base-content/60">{{ $card['label'] }}</p>
                    <p class="text-3xl font-bold {{ $card['class'] }}">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <input type="date" name="from" value="{{ request('from') }}" class="input input-bordered input-sm" />
                <input type="date" name="to" value="{{ request('to') }}" class="input input-bordered input-sm" />
                <select name="status" class="select select-bordered select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Late</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $row)
                            <tr>
                                <td>{{ $row->attendance_date->format('d M Y') }}</td>
                                <td>{{ $row->employee?->full_name ?? '—' }}</td>
                                <td>{{ $row->check_in_time?->format('H:i') ?? '—' }}</td>
                                <td>{{ $row->check_out_time?->format('H:i') ?? '—' }}</td>
                                <td>{{ $row->late_minutes ? $row->late_minutes.'m' : '—' }}</td>
                                <td><span class="badge {{ $row->attendance_status->color() }}">{{ $row->attendance_status->label() }}</span></td>
                                <td>{{ $row->working_hours }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-base-content/50 py-6">No attendance records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $attendances->links() }}</div>
        </div>
    </div>
</x-layouts.app>
