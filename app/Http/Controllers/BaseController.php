<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function handleResponseSuccess($data, $msg){
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $msg
        ];

        return response()->json($response);
    }

    public function handleResponseErros($data,$msg) {
        $response = [
            'success' => false,
            'data' => $data,
            'message' => $msg
        ];

        return response()->json($response);
    }
}
