<?php

namespace App\Traits;

trait ResponseJson
{
    protected function respondSuccess($data, $message, $status = 200)
    {
        $response = [];
        $response['status'] = true;
        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function respondError($data, $message, $status = 500)
    {
        $response = [];
        $response['status'] = false;
        $response['message'] = $message;
        if (!empty($data)) {
            $response['error'] = $data;
        }

        return response()->json($response, $status);
    }
}
