<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function successResponse(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            ...$data,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    protected function errorResponse(string $error, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $error,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}
