<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignUpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Авторизация
    public function login(LoginRequest $request) {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            throw new ApiException(401, 'Authorization failed');
        }

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Success',
            'token' => Auth::user()->generateToken()
        ])->setStatusCode(200);
    }

    // Регистрация
    public function signUp(SignUpRequest $request){

        return response()->json([
            'success' => true,
            'code' => 201,
            'message' => 'Success',
            'token' => User::create($request->all())->generateToken()
        ])->setStatusCode(201);
    }

    // Логаут
    public function logout(Request $request) {
        $request->user()->forceFill(['remember_token' => ''])->save();
        return response()->json()->setStatusCode(204);
    }

}
