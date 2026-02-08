<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::apiResource(name: 'addresses', controller: AddressController::class);
Route::apiResource(name: 'roles', controller: RoleController::class);
Route::apiResource(name: 'users', controller: UserController::class);
Route::get('/version', function (): JsonResponse {
    return response()->json([
        'data' => [
            'version' => config('app.version'),
        ],
    ]);
});
