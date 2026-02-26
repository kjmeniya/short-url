<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * API Version
     */
    protected string $apiVersion = 'v1';

    /**
     * Return a success response
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'api_version' => $this->apiVersion,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a created response
     */
    protected function createdResponse(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => $this->getHttpErrorText($code),
            'code' => $code,
            'api_version' => $this->apiVersion,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Return a service unavailable response
     */
    protected function serviceUnavailableResponse(string $message = 'Service Unavailable'): JsonResponse
    {
        return $this->errorResponse($message, 503);
    }

    /**
     * Return a too many requests response
     */
    protected function tooManyRequestsResponse(string $message = 'Too Many Requests', int $retryAfter = null, int $limit = null, string $limitType = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => $this->getHttpErrorText(429),
            'code' => 429,
            'api_version' => $this->apiVersion,
        ];

        if ($retryAfter !== null) {
            $response['retry_after'] = $retryAfter;
        }
        if ($limit !== null) {
            $response['limit'] = $limit;
        }
        if ($limitType !== null) {
            $response['limit_type'] = $limitType;
        }

        return response()->json($response, 429);
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(mixed $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'api_version' => $this->apiVersion,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Get HTTP error text by code
     */
    protected function getHttpErrorText(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }

    /**
     * Get the current API version
     */
    public function getVersion(): string
    {
        return $this->apiVersion;
    }
}
