<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public route
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'checkUserStatus', 'logRequest'])->group( function(){
    Route::middleware('can:viewAny,App\Models\User')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
    });

    Route::middleware('can:create,App\Models\User')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('can:delete,task');

    Route::get('/logs', [ActivityLogController::class, 'index'])
        ->middleware('can:viewAny,App\Models\ActivityLog');
});
