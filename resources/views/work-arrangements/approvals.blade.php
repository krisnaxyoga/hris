<x-layouts.app title="WFH Approvals">
    <x-ui.page-header title="WFH / Business Trip Approvals" subtitle="Requests awaiting your decision" />

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Employee</th><th>Date</th><th>Mode</th><th>Location</th><th>Reason</th><th class="text-right">Decision</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($pending as $row)
                            <tr>
                                <td class="font-medium">{{ $row->employee->full_name }}</td>
                                <td>{{ $row->attendance_date->format('d M Y') }}</td>
                                <td><span class="badge {{ $row->attendance_mode->badge() }}">{{ $row->attendance_mode->label() }}</span></td>
                                <td>{{ $row->work_location ?? '—' }}</td>
                                <td class="text-sm max-w-xs truncate">{{ $row->reason ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <form method="POST" action="{{ route('work-arrangements.approve', $row) }}">
                                            @csrf
                                            <button class="btn btn-success btn-xs">Approve</button>
                                        </form>
                                        <button class="btn btn-error btn-xs" onclick="war_{{ $row->id }}.showModal()">Reject</button>
                                    </div>
                                    <dialog id="war_{{ $row->id }}" class="modal">
                                        <div class="modal-box">
                                            <h3 class="font-bold text-lg">Reject Request</h3>
                                            <form method="POST" action="{{ route('work-arrangements.reject', $row) }}">
                                                @csrf
                                                <fieldset class="fieldset">
                                                    <legend class="fieldset-legend">Reason</legend>
                                                    <textarea name="rejection_reason" rows="3" required class="textarea textarea-bordered w-full"></textarea>
                                                </fieldset>
                                                <div class="modal-action">
                                                    <button type="button" class="btn btn-ghost" onclick="war_{{ $row->id }}.close()">Cancel</button>
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
