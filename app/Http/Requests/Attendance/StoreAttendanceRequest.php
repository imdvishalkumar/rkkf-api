<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branch,branch_id',
            'date' => 'required|date',
            'attendance' => 'required|array|min:1',
            'attendance.*.student_id' => 'required|exists:students,student_id',
            'attendance.*.attend' => 'required|string|in:P,A,L',
        ];
    }
}



