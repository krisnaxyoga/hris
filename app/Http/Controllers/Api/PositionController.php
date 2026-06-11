<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\StorePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PositionController extends Controller
{
    public function __construct(private readonly PositionService $positions) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Position::class);

        $filters = $request->only(['search', 'department_id']);
        $filters['company_id'] = $request->user()->company_id;

        $positions = $this->positions->paginate($filters, (int) $request->integer('per_page', 15));

        return PositionResource::collection($positions);
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        $this->authorize('create', Position::class);

        $position = $this->positions->create($request->validated());

        return PositionResource::make($position->load(['company', 'department']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Position $position): PositionResource
    {
        $this->authorize('view', $position);

        return PositionResource::make($position->load(['company', 'department']));
    }

    public function update(UpdatePositionRequest $request, Position $position): PositionResource
    {
        $this->authorize('update', $position);

        return PositionResource::make($this->positions->update($position, $request->validated())->load(['company', 'department']));
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->authorize('delete', $position);

        $this->positions->delete($position);

        return response()->json(status: 204);
    }
}
