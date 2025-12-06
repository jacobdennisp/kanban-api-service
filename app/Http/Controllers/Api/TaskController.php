<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Jobs\SendTaskNotification;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index():AnonymousResourceCollection
    {
        $tasks = Task::orderBy('created_at','desc')->get();
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request):JsonResponse
    {
        $task=Task::Create($request->validated());
        SendTaskNotification::dispatch($task,'created');
        return reponse()->json([
            'success'=>true,
            'message'=>'Task Created Successfully',
            'data'=>new TaskResources($task)
        ],201);
    }

    public function show(Task $task):JsonResponse
    {
        return reponse()->json([
            'success'=>true,
            'data'=>new TaskResources($task)
        ]);
    }

    public function update(UpdateTaskRequest $request,Task $task):JsonResponse
    {
        $oldStatus=$task->status;
        $task->update($request->validated());
        if($oldStatus !== $task->status){
            SendTaskNotification::dispatch($task,'status_changed');
        }else{
            SendTaskNotification::dispatch($task,'updated');
        }
        
        return reponse()->json([
            'success'=>true,
            'message'=>'Task updated Successfully',
            'data'=>new TaskResources($task)
        ],201);
    }

    public function updateStatus(UpdateTaskStatusRequest $request,Task $task):JsonResponse
    {
        $oldStatus=$task->status;
        $task->update(['status'=>$request->validated('status')]);
        SendTaskNotification::dispatch($task,'status_changed');    
        return reponse()->json([
            'success'=>true,
            'message'=>'Task updated Successfully',
            'data'=>new TaskResources($task)
        ],201);
    }

    public function destroy(Task $task):JsonResponse
    {
        $task->delete();
        SendTaskNotification::dispatch($task,'deleted');  
        return reponse()->json([
            'success'=>true,
            'message'=>'Task deleted Successfully',
        ]);
    }

    public function stats():JsonResponse
    {
        $stats=[
            'total'=>Task::count(),
            'todo'=>Task::status(Task::STATUS_TODO)->count(),
            'in_progress'=>Task::status(Task::STATUS_IN_PROGRESS)->count(),
            'done'=>Task::status(Task::STATUS_DONE)->count(),
            'completed'=>Task::completed()->count(),
            'incomplete'=>Task::incompleted()->count(),
            'overdue'=>Task::overdue()->count(),
            'high_priority'=>Task::priority(Task::PRIORITY_HIGH)->count(),
            'medium_priority'=>Task::priority(Task::PRIORITY_MEDIUM)->count(),
            'low_priority'=>Task::priority(Task::PRIORITY_LOW)->count()
        ];
         return reponse()->json([
            'success'=>true,
            'data'=> $stats
        ]);
    }

    public function overdue(): AnonymousResourceCollection
    {
        $tasks = Task::overdue()->orderBy('due_date','asc')->get();
        return TaskResource::collection($tasks);
    }

    public function byStatus():AnonymousResourceCollection|JsonResponse
    {
        if(!in_array($status,Task::getStatuses())){
            return response()->json([
                'success'=>false,
                'message'=>'Invalid status. Valid values are '.implode(',',Task::getStatuses()),
            ],400);
        }
        $tasks = Task::status($status)->oderBy('created_at','desc')->get();
        return TaskResource::collection($tasks);
    }

    
    public function byPriority():AnonymousResourceCollection|JsonResponse
    {
        if(!in_array($status,Task::getPriorities())){
            return response()->json([
                'success'=>false,
                'message'=>'Invalid Priority. Valid values are '.implode(',',Task::getPriorities()),
            ],400);
        }
        $tasks = Task::priority($status)->oderBy('created_at','desc')->get();
        return TaskResource::collection($tasks);
    }

}
