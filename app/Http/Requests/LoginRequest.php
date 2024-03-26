<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends ApiRequest
{
    // Правила валидации при авторизации
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:3',
        ];
    }
}
