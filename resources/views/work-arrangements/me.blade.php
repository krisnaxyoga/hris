<x-layouts.app title="Work Arrangements">
    <x-ui.page-header title="WFH / Business Trip" subtitle="Request to work outside the office" />

    @unless ($employee)
        <div class="alert alert-warning">Your account is not linked to an employee profile. Contact HR.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg">New Request</h2>
                    <form method="POST" action="{{ route('work-arrangements.store') }}">
                        @csrf
                        <x-form.select label="Mode" name="attendance_mode" :options="$modes" required />
                        <x-form.input label="Date" name="attendance_date" type="date" required />
                        <x-form.input label="Work Location" name="work_location" />
                        <x-form.textarea label="Reason" name="reason" />
                        <button type="submit" class="btn btn-primary w-full mt-4">Submit Request</button>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-lg">My Requests</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr><th>Date</th><th>Mode</th><th>Location</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $row)
                                    <tr>
                                        <td>{{ $row->attendance_date->format('d M Y') }}</td>
                                        <td><span class="badge {{ $row->attendance_mode->badge() }}">{{ $row->attendance_mode->label() }}</span></td>
                                        <td>{{ $row->work_location ?? '—' }}</td>
                                        <td><span class="badge {{ $row->status->color() }}">{{ $row->status->label() }}</span></td>
                                        <td class="text-right">
                                            @unless ($row->status->isFinal())
                                                <form method="POST" action="{{ route('work-arrangements.cancel', $row) }}" onsubmit="return confirm('Cancel?')">
                                                    @csrf
                                                    <button class="btn btn-ghost btn-xs text-error">Cancel</button>
                                                </form>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-base-content/50 py-6">No requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($requests)<div class="mt-4">{{ $requests->links() }}</div>@endif
                </div>
            </div>
        </div>
    @endunless
</x-layouts.app>
