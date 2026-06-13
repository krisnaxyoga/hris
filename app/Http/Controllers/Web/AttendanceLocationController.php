<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocation\StoreAttendanceLocationRequest;
use App\Http\Requests\AttendanceLocation\UpdateAttendanceLocationRequest;
use App\Models\AttendanceLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLocationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceLocation::class);

        $locations = AttendanceLocation::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->paginate(15);

        return view('attendance-locations.index', compact('locations'));
    }

    public function create(): View
    {
        $this->authorize('create', AttendanceLocation::class);

        return view('attendance-locations.create');
    }

    public function store(StoreAttendanceLocationRequest $request): RedirectResponse
    {
        $this->authorize('create', AttendanceLocation::class);

        AttendanceLocation::create($request->validated());

        return redirect()->route('attendance-locations.index')->with('success', 'Attendance zone created.');
    }

    public function edit(AttendanceLocation $attendanceLocation): View
    {
        $this->authorize('update', $attendanceLocation);

        return view('attendance-locations.edit', ['location' => $attendanceLocation]);
    }

    public function update(UpdateAttendanceLocationRequest $request, AttendanceLocation $attendanceLocation): RedirectResponse
    {
        $this->authorize('update', $attendanceLocation);

        $attendanceLocation->update($request->validated());

        return redirect()->route('attendance-locations.index')->with('success', 'Attendance zone updated.');
    }

    public function destroy(AttendanceLocation $attendanceLocation): RedirectResponse
    {
        $this->authorize('delete', $attendanceLocation);

        $attendanceLocation->delete();

        return redirect()->route('attendance-locations.index')->with('success', 'Attendance zone deleted.');
    }
}
