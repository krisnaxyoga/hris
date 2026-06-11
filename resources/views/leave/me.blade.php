<x-layouts.app title="My Leave">
    <x-ui.page-header title="My Leave" subtitle="Request time off and track your balance" />

    @unless ($employee)
        <div class="alert alert-warning">Your account is not linked to an employee profile. Contact HR.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Apply form --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg">Request Leave</h2>
                    <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
                        @csrf
                        <x-form.select label="Leave Type" name="leave_type_id" :options="$leaveTypes" required />
                        <x-form.input label="Start Date" name="start_date" type="date" required />
                        <x-form.input label="End Date" name="end_date" type="date" required />
                        <x-form.textarea label="Reason" name="reason" />
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Attachment (optional)</legend>
                            <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg" class="file-input file-input-bordered file-input-sm w-full" />
                        </fieldset>
                        <button type="submit" class="btn btn-primary w-full mt-4">Submit Request</button>
                    </form>
                </div>
            </div>

            {{-- Balances + history --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Leave Balance ({{ now()->year }})</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @forelse ($balances as $balance)
                                <div class="stat bg-base-200 rounded-box">
                                    <div class="stat-title text-xs">{{ $balance->leaveType->name }}</div>
                                    <div class="stat-value text-2xl">{{ $balance->remaining_days }}</div>
                                    <div class="stat-desc">of {{ $balance->entitled_days }} days left</div>
                                </div>
                            @empty
                                <p class="text-sm text-base-content/50">No balances yet — they are created on your first request.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">My Requests</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($requests as $row)
                                        <tr>
                                            <td>{{ $row->leaveType->name }}</td>
                                            <td class="text-sm">{{ $row->start_date->format('d M') }} – {{ $row->end_date->format('d M Y') }}</td>
                                            <td>{{ $row->total_days }}</td>
                                            <td><span class="badge {{ $row->status->color() }}">{{ $row->status->label() }}</span></td>
                                            <td class="text-right">
                                                @if (! $row->status->isFinal())
                                                    <form method="POST" action="{{ route('leave.cancel', $row) }}" onsubmit="return confirm('Cancel this request?')">
                                                        @csrf
                                                        <button class="btn btn-ghost btn-xs text-error">Cancel</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-base-content/50 py-6">No leave requests yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($requests)<div class="mt-4">{{ $requests->links() }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
    @endunless
</x-layouts.app>
