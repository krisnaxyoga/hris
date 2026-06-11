<x-layouts.app title="Leave Approvals">
    <x-ui.page-header title="Leave Approvals" subtitle="Requests awaiting your decision" />

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Stage</th>
                            <th class="text-right">Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pending as $row)
                            <tr>
                                <td class="font-medium">{{ $row->employee->full_name }}</td>
                                <td>{{ $row->leaveType->name }}</td>
                                <td class="text-sm">{{ $row->start_date->format('d M') }} – {{ $row->end_date->format('d M Y') }}</td>
                                <td>{{ $row->total_days }}</td>
                                <td><span class="badge {{ $row->status->color() }}">{{ $row->status->label() }}</span></td>
                                <td class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <form method="POST" action="{{ route('leave.approve', $row) }}">
                                            @csrf
                                            <button class="btn btn-success btn-xs">Approve</button>
                                        </form>
                                        <button class="btn btn-error btn-xs" onclick="reject_{{ $row->id }}.showModal()">Reject</button>
                                    </div>

                                    <dialog id="reject_{{ $row->id }}" class="modal">
                                        <div class="modal-box">
                                            <h3 class="font-bold text-lg">Reject Leave Request</h3>
                                            <form method="POST" action="{{ route('leave.reject', $row) }}">
                                                @csrf
                                                <fieldset class="fieldset">
                                                    <legend class="fieldset-legend">Reason</legend>
                                                    <textarea name="rejection_reason" rows="3" required class="textarea textarea-bordered w-full"></textarea>
                                                </fieldset>
                                                <div class="modal-action">
                                                    <button type="button" class="btn btn-ghost" onclick="reject_{{ $row->id }}.close()">Cancel</button>
                                                    <button type="submit" class="btn btn-error">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop"><button>close</button></form>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-base-content/50 py-6">Nothing awaiting your approval. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
