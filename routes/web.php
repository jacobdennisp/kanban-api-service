<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Services\AmqpNotificationService;
use App\Models\Task;

Route::get('/test-rabbitmq', function (AmqpNotificationService $amqpService) {
    try {
        // Test connection
        $result = $amqpService->testConnection();

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'RabbitMQ connection successful!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'RabbitMQ connection failed. Check logs.',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
Route::get('/', function () {
    return view('welcome');
});
