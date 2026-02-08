<?php

declare(strict_types=1);

namespace App\Http\Controllers\Base;

use App\Contracts\Base\BaseServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * @property BaseServiceInterface $service
 */
abstract class BaseCrudController extends Controller
{
    /**
     * Get the base name for request classes (e.g., 'User', 'Address')
     */
    abstract protected function getRequestBaseName(): string;

    protected BaseServiceInterface $service;

    /**
     * BaseCrudController constructor.
     */
    public function __construct(BaseServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $indexRequestClass = $this->getIndexRequestClass();
        $validatedData = $this->validateForm(requestClass: $indexRequestClass);

        $rows = $this->service->index(request: $validatedData);

        return response()->json(data: ['data' => $rows], status: 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $showRequestClass = $this->getShowRequestClass();
        $validatedData = $this->validateForm(requestClass: $showRequestClass);

        $model = $this->service->find(id: $id, request: $validatedData);

        return response()->json(data: ['data' => $model], status: 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $storeRequestClass = $this->getStoreRequestClass();
        $validatedData = $this->validateForm(requestClass: $storeRequestClass);

        if ($validatedData->isNotEmpty()) {
            $model = $this->service->create(request: $validatedData);

            return response()->json(data: ['data' => $model], status: 201);
        }

        return $this->respondWithEmptyValidationError(
            message: 'No valid fields were submitted for create.',
            field: 'create'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $updateRequestClass = $this->getUpdateRequestClass();
        $validatedData = $this->validateForm(requestClass: $updateRequestClass);

        if ($validatedData->isNotEmpty()) {
            $model = $this->service->update(request: $validatedData, id: $id);

            return response()->json(data: ['data' => $model], status: 200);
        }

        return $this->respondWithEmptyValidationError(
            message: 'No valid fields were submitted for update.',
            field: 'update'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete(id: $id);

        return response()->json(data: ['data' => []], status: 200);
    }

    /**
     * Validate the form
     *
     * @param  string  $requestClass
     */
    private function validateForm($requestClass): Collection
    {
        // Instanciate the form request
        $formRequest = app(abstract: $requestClass);

        // Load JSON, execute authorize() and validate()
        $formRequest->validateResolved();

        return collect(value: $formRequest->validated());
    }

    /**
     * Respond with empty validation error
     */
    protected function respondWithEmptyValidationError(string $message, string $field = 'data'): JsonResponse
    {
        return response()->json(data: [
            'message' => 'The given data was invalid.',
            'errors' => [
                $field => [$message],
            ],
        ], status: 422);
    }

    /**
     * Get the request class for the index action.
     */
    protected function getIndexRequestClass(): string
    {
        return "App\\Http\\Requests\\Index{$this->getRequestBaseName()}Request";
    }

    /**
     * Get the request class for the show action.
     */
    protected function getShowRequestClass(): string
    {
        return "App\\Http\\Requests\\Show{$this->getRequestBaseName()}Request";
    }

    /**
     * Get the request class for the store action.
     */
    protected function getStoreRequestClass(): string
    {
        return "App\\Http\\Requests\\Store{$this->getRequestBaseName()}Request";
    }

    /**
     * Get the request class for the update action.
     */
    protected function getUpdateRequestClass(): string
    {
        return "App\\Http\\Requests\\Update{$this->getRequestBaseName()}Request";
    }

    /**
     * Get the request class for the delete action.
     */
    protected function getDeleteRequestClass(): string
    {
        return "App\\Http\\Requests\\Delete{$this->getRequestBaseName()}Request";
    }
}
