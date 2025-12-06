<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('health',function(){
    return response()->json([
        'status'=>'ok',
        'service'=>'Kanban Todo API',
        'timestamp'=>now()->toIso8601String(),
    ]);
});

Route::prefix('tasks')->group(function(){
    Route::get('stats',[TaskController::class,'stats'])->name('tasks.stats');
    Route::get('overdue',[TaskController::class,'overdue'])->name('tasks.overdue');
    Route::get('status/{status}',[TaskController::class,'byStatus'])->name('tasks.by-status');
    Route::get('priority/{priority}',[TaskController::class,'byPriority'])->name('tasks.by-priority');
    Route::put('{task}/status',[TaskController::class,'updateStatus'])->name('tasks.update-status');
    Route::get('/',[TaskController::class,'index'])->name('tasks.index');
    Route::post('/',[TaskController::class,'store'])->name('tasks.store');
    Route::get('{task}',[TaskController::class,'show'])->name('tasks.show');
    Route::put('{task}',[TaskController::class,'update'])->name('tasks.update');
    Route::delete('{task}',[TaskController::class,'delete'])->name('tasks.delete');
});