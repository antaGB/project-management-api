<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'project_id'  => 'sometimes|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title'       => 'sometimes|string|max:255',
            'description'       => 'string|max:700',
            'priority'    => 'sometimes|in:low,medium,high',
            'status'    => 'sometimes|in:to-do,in-progress,done',
        ];
    }
}
