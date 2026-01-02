<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Use policy/middleware for authorization
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255|alpha',
            'gender' => 'required|integer|in:1,2',
            'email' => 'required|email|unique:students,email|unique:users,email',
            'belt_id' => 'required|exists:belt,belt_id',
            'dadno' => 'required|string|size:10|regex:/^[0-9]+$/',
            'dadwp' => 'required|string|size:10|regex:/^[0-9]+$/',
            'momno' => 'required|string|size:10|regex:/^[0-9]+$/',
            'momwp' => 'required|string|size:10|regex:/^[0-9]+$/',
            'selfno' => 'required|string|size:10|regex:/^[0-9]+$/',
            'swno' => 'required|string|size:10|regex:/^[0-9]+$/',
            'dob' => 'required|date',
            'doj' => 'required|date',
            'address' => 'required|string|max:500',
            'branch_id' => 'required|exists:branch,branch_id',
            'pincode' => 'required|string|size:6|regex:/^[0-9]+$/',
            'fees' => 'required|numeric|min:0',
            'months' => 'required|array|min:1',
            'months.*' => 'integer|between:1,12',
        ];
    }
}



