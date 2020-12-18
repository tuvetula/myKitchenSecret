<?php


namespace App\Http\Business;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ResponseJsonBusiness
{
    /**
     * success response method.
     *
     * @param array|JsonResource $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function sendSuccess($data, string $message, $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param string $message
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public static function sendError(string $message, $errorMessages = [], $code = Response::HTTP_NOT_FOUND): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
