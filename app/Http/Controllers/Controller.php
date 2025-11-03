<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Send Success API Response
     *
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendSuccessApiResponse($message, $data, $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Send Error API Response
     *
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendErrorApiResponse($message, $status = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $status);

    }
}
