<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ProjectUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/login', function () {
    return response()->json(['message' => 'Logged in']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes that require authentication (Passport)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/test', function () {
        return response()->json(['message' => 'Authenticated Route Accessed']);
    });

    Route::get('/projects', [ProjectController::class, 'index']);
    // Route::get('/projects/filter', [ProjectController::class, 'filter']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}',[ProjectController::class, 'destroy']);

    Route::get('/attributes', [AttributeController::class, 'index']);
    Route::get('/attributes/{id}',  [AttributeController::class, 'show']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::put('/attributes/{id}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{id}',[AttributeController::class, 'destroy']);

    Route::post('/projects/{id}/users', [ProjectUserController::class, 'addUser']);

    Route::get('/timesheets',[TimesheetController::class, 'index']);
    Route::get('/timesheets/{id}', [TimesheetController::class, 'show']);
    Route::post('/timesheets', [TimesheetController::class, 'store']);
    Route::put('/timesheets/{id}', [TimesheetController::class, 'update']);
    Route::delete('/timesheets/{id}',[TimesheetController::class, 'destroy']);
});
