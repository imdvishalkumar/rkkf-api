<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch');

        return [
            'branch_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('branch', 'name')->ignore($branchId, 'branch_id'),
            ],
            'branch_fees' => 'sometimes|required|numeric|min:0',
            'late' => 'sometimes|required|numeric|min:0',
            'discount' => 'sometimes|required|numeric|min:0',
            'days' => 'sometimes|required|array|min:1',
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ];
    }
}



