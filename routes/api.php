<?php

use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post("login", [AuthController::class, "login"]);
Route::middleware('auth:api')->group(function () {
  Route::controller(StudentController::class)->group(function () {
    Route::post('crud/listing', 'index');
    Route::post('crud/add', 'store');
    Route::get('crud/edit/{id}', 'show');
    Route::post('crud/update', 'update');
    Route::post('crud/update-status', 'updateStatus');
    Route::post('crud/deleteAll', 'deleteAll');
  });

  Route::get('/logout', [AuthController::class, 'logout']);
  Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::any('{any}', function () {
  $response = [
    'settings' => ['success' => 0, 'message' => 'Page not found'],
    'data'    => [],
  ];
  return response()->json($response, 404);
})->where('any', '.*');
