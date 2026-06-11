<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\StorePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Models\Department;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(private readonly PositionService $positions) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Position::class);

        $filters = $request->only(['search', 'department_id']);
        $filters['company_id'] = $request->user()->company_id;

        $positions = $this->positions->paginate($filters);

        return view('positions.index', [
            'positions' => $positions,
            'departments' => $this->departmentOptions($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Position::class);

        return view('positions.create', ['departments' => $this->departmentOptions($request)]);
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $this->authorize('create', Position::class);

        $this->positions->create($request->validated());

        return redirect()->route('positions.index')->with('success', 'Position created.');
    }

    public function edit(Request $request, Position $position): View
    {
        $this->authorize('update', $position);

        return view('positions.edit', [
            'position' => $position,
            'departments' => $this->departmentOptions($request),
        ]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $this->positions->update($position, $request->validated());

        return redirect()->route('positions.index')->with('success', 'Position updated.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->authorize('delete', $position);

        $this->positions->delete($position);

        return redirect()->route('positions.index')->with('success', 'Position deleted.');
    }

    /**
     * @return array<int, string>
     */
    private function departmentOptions(Request $request): array
    {
        return Department::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
