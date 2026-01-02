<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedBranchAccessException extends Exception
{
    protected $message = 'You do not have access to this branch';
    protected $code = 403;

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => $this->getMessage(),
                'data' => null,
                'errors' => null,
            ], $this->getCode());
        }

        abort(403, $this->getMessage());
    }
}



