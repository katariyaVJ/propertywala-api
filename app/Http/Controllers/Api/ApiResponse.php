<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Standard Success Response conforming to Flutter JSON Contract
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        int $statusCode = 200,
        ?array $meta = null
    ): JsonResponse {
        $payload = [
            'success' => true,
            'status' => 'success',
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * Standard Error Response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'status' => 'error',
            'statusCode' => $statusCode,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }
}

