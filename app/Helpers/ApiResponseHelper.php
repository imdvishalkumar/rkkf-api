<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponseHelper
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0',
            ],
        ], $code);
    }

    /**
     * Error response
     */
    public static function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0',
            ],
        ], $code);
    }

    /**
     * Paginated response
     */
    public static function paginated(LengthAwarePaginator $data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
            ],
            'errors' => null,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0',
            ],
        ]);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return self::error($message, 500);
    }

    /**
     * Get valid HTTP status code from exception
     */
    public static function getStatusCode(\Exception $e, int $default = 500): int
    {
        $code = $e->getCode();
        return (is_numeric($code) && $code > 0 && $code < 600) ? (int)$code : $default;
    }

    /**
     * Get role value from enum or integer
     */
    public static function getRoleValue($role): int
    {
        if ($role instanceof \BackedEnum) {
            return $role->value;
        }
        return (int)$role;
    }
}



