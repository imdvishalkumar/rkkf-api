<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    /**
     * Format pagination data to standard format
     * 
     * Returns:
     * {
     *   "data": [...],
     *   "pagination": {
     *     "total": 50,
     *     "per_page": 10,
     *     "current_page": 1,
     *     "total_page": 5,
     *     "has_more": true
     *   }
     * }
     */
    public static function formatPagination(LengthAwarePaginator $paginator)
    {
        return [
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ]
        ];
    }

    /**
     * Format pagination with custom data
     */
    public static function formatWithData(LengthAwarePaginator $paginator, array $data)
    {
        return [
            'data' => $data,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ]
        ];
    }

    /**
     * Get default per page value
     */
    public static function getDefaultPerPage()
    {
        return 10;
    }

    /**
     * Get maximum per page value
     */
    public static function getMaxPerPage()
    {
        return 100;
    }
}
