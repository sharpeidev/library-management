<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponses
{
    /**
     * @param string $message
     * @param string|int $status
     * @param array $data
     * @return JsonResponse
     */
    public function response(string $message, string|int $status, array $data = []): JsonResponse
    {
        $return_data = [
            'message' => $message,
            'status' => $status,
        ];

        if ($data) {
            $return_data['data'] = $data;
        }

        return response()->json($return_data, $status);
    }

    /**
     * @param string $message
     * @param string|int $status
     * @param string $errors
     * @param array $data
     * @return JsonResponse
     */
    public function error(string $message, string|int $status, string $errors, array $data = []): JsonResponse
    {
        $return_data = [
            'message' => $message,
            'status' => $status,
            'errors' => $errors,
        ];

        if ($data) {
            $return_data['data'] = $data;
        }

        return response()->json($return_data, $status);
    }
}
