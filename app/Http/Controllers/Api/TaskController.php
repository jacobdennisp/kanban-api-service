<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\AmqpNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    protected AmqpNotificationService $amqpService;

    public function __construct(AmqpNotificationService $amqpService)
    {
        $this->amqpService = $amqpService;
    }

    /**
     * Display a listing of tasks
     * GET /api/tasks
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task
     * POST /api/tasks
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        //dd(111111);
        $task = Task::create($request->validated());
        
        // Send notification to RabbitMQ
        $this->amqpService->sendTaskNotification($task, 'created');
        
        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => new TaskResource($task)
        ], 201);
    }

    /**
     * Display the specified task
     * GET /api/tasks/{task}
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new TaskResource($task)
        ]);
    }

    /**
     * Update the specified task
     * PUT /api/tasks/{task}
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $oldStatus = $task->status;
        $task->update($request->validated());
        
        // Send notification based on whether status changed
        if ($oldStatus !== $task->status) {
            $this->amqpService->sendTaskNotification($task, 'status_changed');
        } else {
            $this->amqpService->sendTaskNotification($task, 'updated');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task)
        ]);
    }

    /**
     * Update task status (for drag & drop)
     * PUT /api/tasks/{task}/status
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $task->update(['status' => $request->validated()['status']]);
        
        // Send notification to RabbitMQ
        $this->amqpService->sendTaskNotification($task, 'status_changed');
        
        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => new TaskResource($task)
        ]);
    }

    /**
     * Remove the specified task
     * DELETE /api/tasks/{task}
     */
    public function delete(Task $task): JsonResponse
    {
        // Send notification before deleting
        $this->amqpService->sendTaskNotification($task, 'deleted');
        
        $task->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Get task statistics
     * GET /api/tasks/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Task::count(),
            'todo' => Task::status(Task::STATUS_TODO)->count(),
            'wip' => Task::status(Task::STATUS_INPROGRESS)->count(),
            'done' => Task::status(Task::STATUS_DONE)->count(),
            'completed' => Task::completed()->count(),
           // 'incomplete' => Task::inCompleted()->count(),
            'overdue' => Task::overdue()->count(),
            'high_priority' => Task::priority(Task::PRIORITY_HIGH)->count(),
            'medium_priority' => Task::priority(Task::PRIORITY_MEDIUM)->count(),
            'low_priority' => Task::priority(Task::PRIORITY_LOW)->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get overdue tasks
     * GET /api/tasks/overdue
     */
    public function overdue(): AnonymousResourceCollection
    {
        $tasks = Task::overdue()->orderBy('due_date', 'asc')->get();
        return TaskResource::collection($tasks);
    }

    /**
     * Get tasks by status
     * GET /api/tasks/status/{status}
     */
    public function byStatus(string $status): AnonymousResourceCollection|JsonResponse
    {
        if (!in_array($status, Task::getStatuses())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Valid values are: ' . implode(', ', Task::getStatuses()),
            ], 400);
        }
        
        $tasks = Task::status($status)->orderBy('created_at', 'desc')->get();
        return TaskResource::collection($tasks);
    }

    /**
     * Get tasks by priority
     * GET /api/tasks/priority/{priority}
     */
    public function byPriority(string $priority): AnonymousResourceCollection|JsonResponse
    {
        if (!in_array($priority, Task::getPriorities())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid priority. Valid values are: ' . implode(', ', Task::getPriorities()),
            ], 400);
        }
        
        $tasks = Task::priority($priority)->orderBy('created_at', 'desc')->get();
        return TaskResource::collection($tasks);
    }
}