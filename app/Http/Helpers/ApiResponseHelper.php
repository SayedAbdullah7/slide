<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Helper class to format API responses consistently
 * Can be used in Middleware and other non-controller contexts
 */
class ApiResponseHelper
{
    /**
     * Format API response data - matches ApiResponseTrait structure
     */
    private static function formatResponse(array $data, int $statusCode): array
    {
        $responseStructure = [
            'success' => $data['success'] ?? true,
            'message' => $data['message'] ?? null,
            'result' => $data['result'] ?? null,
        ];

        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }

        // Check if success is explicitly false
        if (isset($data['success']) && $data['success'] === false) {
            $responseStructure['error_code'] = $data['error_code'] ?? 1;
        }

        return [
            'content' => $responseStructure,
            'statusCode' => $statusCode,
            'headers' => [],
        ];
    }

    /**
     * Create API response
     */
    private static function apiResponse(array $data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        $formatted = self::formatResponse($data, $statusCode);
        return response()->json($formatted['content'], $formatted['statusCode'], $headers);
    }

    /**
     * Respond with bad request error
     */
    public static function badRequest(string $message = 'Bad Request', array $errors = []): JsonResponse
    {
        return self::apiResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 400);
    }

    /**
     * Respond with error
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        ?\Exception $exception = null,
        int|string $errorCode = 1,
        $result = null
    ): JsonResponse {
        $data = [
            'success' => false,
            'message' => $message ?? 'There was an internal error, please try again later',
            'error_code' => $errorCode,
            'result' => $result,
        ];

        if ($exception && config('app.env') !== 'production') {
            $data['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
            ];
        }

        return self::apiResponse($data, $statusCode);
    }

    /**
     * Respond with custom API response
     */
    public static function response(array $data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return self::apiResponse($data, $statusCode, $headers);
    }
}
