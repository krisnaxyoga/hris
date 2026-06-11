<x-layouts.app title="Dashboard">
    <x-ui.page-header title="Dashboard" subtitle="Overview of your organization" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['label' => 'Employees', 'value' => $stats['employees'], 'route' => 'employees.index'],
                ['label' => 'Departments', 'value' => $stats['departments'], 'route' => 'departments.index'],
                ['label' => 'Positions', 'value' => $stats['positions'], 'route' => 'positions.index'],
                ['label' => 'Users', 'value' => $stats['users'], 'route' => 'users.index'],
            ];
        @endphp
        @foreach ($cards as $card)
            <a href="{{ route($card['route']) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <p class="text-sm text-base-content/60">{{ $card['label'] }}</p>
                    <p class="text-3xl font-bold">{{ number_format($card['value']) }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <h2 class="text-lg font-semibold mb-3">Today's Attendance</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @php
            $widgetCards = [
                ['label' => 'Office', 'value' => $widgets['office_today'], 'class' => 'text-primary'],
                ['label' => 'WFH', 'value' => $widgets['wfh_today'], 'class' => 'text-accent'],
                ['label' => 'Business Trip', 'value' => $widgets['business_trip_today'], 'class' => 'text-secondary'],
                ['label' => 'Late', 'value' => $widgets['late_today'], 'class' => 'text-warning'],
                ['label' => 'Absent', 'value' => $widgets['absent_today'], 'class' => 'text-error'],
            ];
        @endphp
        @foreach ($widgetCards as $card)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <p class="text-xs text-base-content/60">{{ $card['label'] }} Today</p>
                    <p class="text-2xl font-bold {{ $card['class'] }}">{{ number_format($card['value']) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-lg">Recent Employees</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEmployees as $employee)
                            <tr>
                                <td class="font-mono text-sm">{{ $employee->employee_code }}</td>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->department?->name ?? '—' }}</td>
                                <td>{{ $employee->position?->name ?? '—' }}</td>
                                <td><span class="badge {{ $employee->employment_status->color() }}">{{ $employee->employment_status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-base-content/50 py-6">No employees yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
