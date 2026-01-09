<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;

class StoreEventCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Allowed: User and Instructor
        // Blocked: Admin
        if ($user->isAdmin()) {
            return false;
        }

        return true;
        // Note: The UserRole enum logic inside user->isAdmin() handles strict checking.
        // Assuming non-admins (students/instructors) are allowed.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:event_comments,id'],
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Comment content is required.',
            'parent_id.exists' => 'The parent comment does not exist.',
        ];
    }
}
