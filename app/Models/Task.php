<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable=[
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at'
    ];

    protected $casts=[
        'due_date'=>'date',
        'completed_at'=>'datetime',
        'created_at'=>'datetime',
        'updated_at'=>'datetime',
        'deleted_at'=>'datetime'
    ];

    //Status Contants
    const STATUS_TODO='todo';
    const STATUS_IN_PROGRESS='in_progress';
    const STATUS_DONE='done';

    //Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM='medium';
    const PRIORITY_HIGH='high';

    public static function getStatuses():array
    {
        return [
            self::STATUS_TODO,
            self::STATUS_IN_PROGRESS,
            self::STATUS_DONE
        ];
    }

    
    public static function getPriorities():array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH
        ];
    }

    public function scopeStatus($query,string $status){
        return $query->where('status',$status);
    }

    public function scopePriority($query,string $priority){
        return $query->where('priority',$priority);
    }

    public function scopeOverdue($query){
        return $query->where('due_date','<',now())->where('status','!=',self::STATUS_DONE);
    }

    public function scopeCompleted($query){
        return $query->where('status',self::STATUS_DONE);
    }

    public function scopeInComplete($query){
        return $query->where('status','!=',self::STATUS_DONE);
    }

    public function isOverdue():bool
    {
        if(!$this->due_date || $this->status === self::STATUS_DONE){
            return false;
        }
        return $this->due_date->lt(now()->startOfDay());
    }

    public function isCompleted():bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function markAsCompleted():bool
    {
        $this->status = self::STATUS_DONE;
        $this->completed_at = now();
        return $this->save();
    }

    public function markAsInComplete():bool
    {
        $this->status = self::STATUS_TODO;
        $this->completed_at = null;
        return $this->save();
    }

    public static function booted():void
    {
        static::updating(function($task){
            if($task->isDirty('status')){
                if($this->status === self::STATUS_DONE && !$task->completed_at){
                    $task->completed_at = now();
                }elseif($task->status !== self::STATUS_DONE){
                    $task->completed_at = null;
                }
            }
        });
    }

    public function getPriorityLabelAttribute():string
    {
        return ucfirst($this->priority);
    }

    public function getStatusLabelAttribute():string
    {
        return ucfirst(str_replace('_','',$this->status));
    }
}
