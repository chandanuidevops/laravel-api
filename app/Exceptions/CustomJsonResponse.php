<?php

namespace App\Exceptions;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Exception;

class CustomJsonResponse extends JsonResponse
{
    public static function response($code, $data=null, $message=null, $count=0)
    {
        $json = [
            'data' => $data != null ? $data : [],
            'status' => $code == Response::HTTP_OK ? 'success' : 'failed',
            'code' => $code,
            "message" => $message == null ? [] : $message,
            'count' => $count,
        ];
        return new JsonResponse($json, $code);
    }
    private static function validatorArrayGenerator($errorKey, $errorValue)
    {
        return array($errorKey => $errorValue);
    }
    private static function responseMessageGenerator($errorMessage)
    {
        $messageResponse = [];
        $validatorArray = array_map(
            'self::validatorArrayGenerator',
            $errorMessage->keys(), $errorMessage->all()
        );
        foreach($validatorArray as $error)
        {
            $messageResponse = array_merge($messageResponse, $error);
        }
        return $messageResponse;
    }

    public static function validatorResponse($code, $data=null, $message=null, $count=0, $status="failed")
    {
        if (is_object($message))
        {
            $message = self::responseMessageGenerator($message);
        }
        return Response()->json(
            [
                'data' => $data!=null ? $data:[],
                'status' => $status,
                'code' => $code,
                "error" => $message === null ? [] : $message,
                "message" => [],
                'count' => $count,
            ], $code
        );
    }
}
