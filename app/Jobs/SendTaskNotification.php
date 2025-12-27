<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries=3;
    public $backoff=10;

    protected Task $task;

    protected string $action;
    /**
     * Create a new job instance.
     */
    public function __construct(Task $task,string $action)
    {
        $this->task = $task;
        $this->action=$action;
        $this->onQueue('kanban_tasks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            $notificationData = [
                'task_id'=>$this->task->id,
                'title'=>$this->task->title,
                'status'=>$this->task->status,
                'priority'=>$this->task->priority,
                'action'=>$this->action,
                'timestamp'=>now()->toIso8601String()
            ];
            Log::info("Sending Task notification",$notificationData);

            $response = Http::timeoute(config('services.notification.timeout',30))
                        ->retry(config('services.notification.retry_times',3),100)
                        ->post(config('services.notification.url').'/api/notifications',$notificiationData);

            if($response->successful()){
                Log::info('Task notification sent successfully',[
                    'task_id'=>$this->task->id,
                    'action'=>$this->action
                ]);
            }else{
                Log::error('Failed to send Task notification',[
                    'task_id'=>$this->task->id,
                    'action'=>$this->action,
                    'status_code'=>$response->status(),
                    'response'=>$response->body(),
                ]);
            }

        }catch(\Exception $e){
            Log::error('Exception while sendign task notification',[
                'task_id'=>$this->task->id,
                'action'=>$this->action,
                'error'=>$e->getMessage(),
                'trace'=>$e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception){
        Log::error('Task notification job failed after all retires',[
            'task_id'=>$this->task->id,
            'action'=>$this->action,
            'error'=>$exception->getMessage(),
        ]);
    }
}
