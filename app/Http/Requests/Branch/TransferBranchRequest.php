<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class TransferBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_branch_id' => 'required|exists:branch,branch_id',
            'to_branch_id' => 'required|exists:branch,branch_id|different:from_branch_id',
        ];
    }
}



