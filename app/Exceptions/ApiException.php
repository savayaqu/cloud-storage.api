<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiException extends HttpResponseException
{
    // Конструктор исключения
    public function __construct($code, $message, $errors = [])
    {
        $request = [
            'success' => false,
            'code' => $code,
        ];

        if (!empty($message)) {
            $request['message'] = $message;
        }

        if (count($errors) > 0) {
            $request['message'] = $errors;
        }
        parent::__construct( response()->json($request)->setStatusCode($code));
    }
}
