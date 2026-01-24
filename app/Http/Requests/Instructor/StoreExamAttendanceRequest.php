<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by route middleware
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
            'exam_id' => 'required|integer|exists:exam,exam_id',
            'attendanceArray' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'exam_id.required' => 'Exam ID is required.',
            'exam_id.integer' => 'Exam ID must be a valid integer.',
            'exam_id.exists' => 'The selected exam does not exist.',
            'attendanceArray.required' => 'Attendance data is required.',
            'attendanceArray.string' => 'Attendance data must be a JSON string.',
        ];
    }

    /**
     * Get decoded attendance array.
     */
    public function getDecodedAttendanceArray(): ?array
    {
        $data = json_decode($this->input('attendanceArray'), true);
        return is_array($data) ? $data : null;
    }
}
