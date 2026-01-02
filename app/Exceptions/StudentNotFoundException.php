<?php

namespace App\Exceptions;

use Exception;

class StudentNotFoundException extends Exception
{
    protected $message = 'Student not found';
    protected $code = 404;

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

        return redirect()->back()->with('error', $this->getMessage());
    }
}



