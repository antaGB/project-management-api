<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id'  => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title'       => 'required|string|max:255',
            'description'       => 'string|max:700',
            'priority'    => 'required|in:low,medium,high',
            'status'    => 'required|in:to-do,in-progress,done',
        ];
    }
}
