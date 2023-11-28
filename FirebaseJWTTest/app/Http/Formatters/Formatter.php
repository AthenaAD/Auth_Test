<?php

namespace App\Http\Formatters;

use Laravel\Nova\Fields\Boolean;

class Formatter
{
    public function formatResponse(bool $isSuccess,string $message,string $detail, array $data = null, int $statusCode): \Illuminate\Http\JsonResponse
    {
        $response = [
            'status' => [
                'is_success' => $isSuccess,
                'message' => $message,
                'detail' => $detail
            ],
            'data' => $data
        ];
    
        return response()->json($response, $statusCode);
    }
}