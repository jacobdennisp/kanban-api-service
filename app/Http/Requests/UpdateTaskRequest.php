<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'=>['somtimes','required','string','max:255'],
            'description'=>['nullable','string','max:1000'],
            'status'=>['sometimes',Rule::in(Task::getStatuses())],
            'priority'=>['sometimes','required',Rule::in(Task::getPriorities())],
            'due_date'=>['nullable','date','after_or_equal:today']
        ];
    }

    public function messages():array
    {
        return [
            'title.required'=>'The task title is required',
            'title.max'=>'The title must not exceed 255 characters',
            'description.max'=>'The task description must not exceed 1000 characters',
            'status.required'=>'The task status is required.',
            'status.in'=>'The selected status is invalid. Valid values are todo,in_progress,done.',
            'priority.required'=>'The task priority is required.',
            'priority.in'=>'The selected p riority is invalid. Valid ones are low, medium,high.',
            'due_date.date'=>'The due date must be a valid date.',
            'due_date.after_or_equal'=>'The due date must be today or a future date'
        ];
    }
}
