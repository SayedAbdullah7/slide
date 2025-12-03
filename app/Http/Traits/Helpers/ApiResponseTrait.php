<?php

namespace App\Http\Traits\Helpers;

//use App\Http\Resources\Ghost\EmptyResource;
//use App\Http\Resources\Ghost\EmptyResourceCollection;
use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    /**
     * Return a resource as response.
     */
    protected function respondWithResource(JsonResource $resource, ?string $message = null, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return $this->apiResponse([
            'success' => true,
            'result' => $resource,
            'message' => $message
        ], $statusCode, $headers);
    }

    /**
     * Return a resource collection as response.
     */
    protected function respondWithResourceCollection(ResourceCollection $resourceCollection, ?string $message = null, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return $this->apiResponse([
            'success' => true,
            'result' => $resourceCollection->response()->getData(),
            'message' => $message
        ], $statusCode, $headers);
    }

    /**
     * Standardize API response structure.
     */
    protected function parseGivenData(array $data = [], int $statusCode = 200, array $headers = []): array
    {
        $responseStructure = [
            'success' => $data['success'] ?? true,
            'message' => $data['message'] ?? null,
            'result' => $data['result'] ?? null,
        ];

        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }

        if (isset($data['status'])) {
            $statusCode = $data['status'];
        }

        if (isset($data['exception']) && ($data['exception'] instanceof Error || $data['exception'] instanceof Exception)) {
            if (config('app.env') !== 'production') {
                $responseStructure['exception'] = [
                    'message' => $data['exception']->getMessage(),
                    'file' => $data['exception']->getFile(),
                    'line' => $data['exception']->getLine(),
                    'code' => $data['exception']->getCode(),
                    'trace' => $data['exception']->getTrace(),
                ];
            }

            if ($statusCode === 200) {
                $statusCode = 500;
            }
        }

        if ($data['success'] === false) {
            $responseStructure['error_code'] = $data['error_code'] ?? 1;
        }

        return [
            "content" => $responseStructure,
            "statusCode" => $statusCode,
            "headers" => $headers
        ];
    }

    /**
     * Return generic json response with given data.
     */
    protected function apiResponse(array $data = [], int $statusCode = 200, array $headers = []): JsonResponse
    {
        $result = $this->parseGivenData($data, $statusCode, $headers);

        return response()->json($result['content'], $result['statusCode'], $result['headers']);
    }

    /**
     * Respond with success message only.
     */
    protected function respondSuccess(string $message = ''): JsonResponse
    {
        return $this->apiResponse(['success' => true, 'message' => $message]);
    }

    /**
     * Respond with success and single result data.
     */
    protected function respondSuccessWithData(string $message = '', $data = []): JsonResponse
    {
        return $this->apiResponse(['success' => true, 'message' => $message, 'result' => $data]);
    }

    /**
     * Respond with success and multiple results (array of key => value).
     */
    protected function respondSuccessWithResults(string $message = '', array $data = []): JsonResponse
    {
        return $this->apiResponse(['success' => true, 'message' => $message, 'result' => $data]);
    }

    /**
     * Respond with created (201).
     */
    protected function respondCreated($data): JsonResponse
    {
        return $this->apiResponse($data, 201);
    }

    /**
     * Respond with no content (204).
     */
    protected function respondNoContent(string $message = 'No Content Found'): JsonResponse
    {
        return $this->apiResponse(['success' => false, 'message' => $message], 204);
    }

//    protected function respondNoContentResource(string $message = 'No Content Found'): JsonResponse
//    {
//        return $this->respondWithResource(new EmptyResource([]), $message, 204);
//    }

//    protected function respondNoContentResourceCollection(string $message = 'No Content Found'): JsonResponse
//    {
//        return $this->respondWithResourceCollection(new EmptyResourceCollection([]), $message, 204);
//    }

    /**
     * Respond with common error responses.
     */
    protected function respondUnAuthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->respondError($message, 401,null,401);
    }

    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->respondError($message, 403);
    }

    protected function respondNotFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->respondError($message, 404);
    }

    protected function respondBadRequest(string $message = 'Bad Request', array $errors = []): JsonResponse
    {
        return $this->apiResponse(['success' => false, 'message' => $message, 'errors' => $errors], 400);
    }

    protected function respondConflict(string $message = 'Conflict'): JsonResponse
    {
        return $this->respondError($message, 409);
    }

    protected function respondTooManyRequests(string $message = 'Too Many Requests'): JsonResponse
    {
        return $this->respondError($message, 429);
    }

    protected function respondServiceUnavailable(string $message = 'Service Unavailable'): JsonResponse
    {
        return $this->respondError($message, 503);
    }

    protected function respondInternalError(string $message = 'Internal Error'): JsonResponse
    {
        return $this->respondError($message, 500);
    }

    /**
     * Respond with error.
     */
    protected function respondError($message, int $statusCode = 400, Exception $exception = null, int|string $error_code = 1, $result = null): JsonResponse
    {
        return $this->apiResponse([
            'success' => false,
            'message' => $message ?? 'There was an internal error, please try again later',
            'exception' => $exception,
            'error_code' => $error_code,
            'result' => $result
        ], $statusCode);
    }

    /**
     * Respond with validation errors (422).
     */
    protected function respondValidationErrors(ValidationException $exception): JsonResponse
    {
        return $this->apiResponse([
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => $exception->errors()
        ], 422);
    }

    /**
     * Respond with validation errors from validator instance (422).
     */
    protected function respondValidationError($errors, string $message = null): JsonResponse
    {
        // If no custom message provided, use a generic Arabic message
        if ($message === null) {
            $message = 'يرجى تصحيح الأخطاء التالية';
        }

        return $this->apiResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Respond with paginated resource.
     */
    protected function respondPaginated(ResourceCollection $resourceCollection, ?string $message = null, int $statusCode = 200, array $headers = []): JsonResponse
    {
        $pagination = $resourceCollection->resource->toArray();

        return $this->apiResponse([
            'success' => true,
            'message' => $message,
            'result' => $pagination['data'] ?? [],
            'meta' => [
                'current_page' => $pagination['current_page'] ?? null,
                'last_page' => $pagination['last_page'] ?? null,
                'per_page' => $pagination['per_page'] ?? null,
                'total' => $pagination['total'] ?? null,
            ]
        ], $statusCode, $headers);
    }

    /**
     * Respond with token (e.g. after login).
     */
    protected function respondWithToken(string $token, string $message = 'Authenticated', array $extra = []): JsonResponse
    {
        return $this->apiResponse([
            'success' => true,
            'message' => $message,
            'result' => array_merge([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], $extra)
        ]);
    }
}
