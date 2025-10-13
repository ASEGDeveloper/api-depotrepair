<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    public function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Return a failed JSON response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $data
     * @return JsonResponse
     */
    public function failedResponse(string $message = 'Failed', int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $errors
     * @return JsonResponse
     */
    public function errorResponse(string $message = 'Error', int $status = 500, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $status);
    }


    public function response(string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message, 
        ], $status);
    }

}
