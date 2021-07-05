<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

class ResponseHelper 
{
    public static function response($data=null, $status=200)
    {
        $response = [
            'url'       => URL::full(),
            'method'    => Request::getMethod(),
            'code'      => $status,
            'payload'   => $data
        ];

        return response($response, 200);
    }
    
    public static function notFoundResponse($message)
    {
        $response = [
            'url' => URL::full(),
            'method' => Request::getMethod(),
            'code' => 404,
            'message' => $message
        ];

        return response($response, 404);
    }
}