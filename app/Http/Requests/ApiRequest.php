<?php

namespace App\Http\Requests;

use App\Exceptions\ApiException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    // Ошибка авторизации
    protected function failedAuthorization()
    {
        throw new ApiException(403, 'Authorization failed');
    }

    // Ошибка валидации данных
    protected function failedValidation(Validator $validator)
    {
        throw new ApiException(422, '', $validator->errors());
    }
}
