<?php

namespace App\Traits;

trait ApiResponse
{
    public function successResponse($message = 'success', $code = 200, $businessCode = 'OK')
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'code'    => $businessCode,
        ], $code);
    }

    public function dataResponse($data = null, $message = 'success', $code = 200, $status = true)
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function errorResponse($error = 'error', $message = 'fail', $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'error'   => $error,
        ], $code);
    }
}
