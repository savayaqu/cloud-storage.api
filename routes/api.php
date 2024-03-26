<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\FileController;
use \App\Http\Controllers\AccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Аутентификация
Route::post('/authorization', [AuthController::class, 'login']);

// Регистрация
Route::post('/registration', [AuthController::class, 'signUp']);

// Для авторизованных пользователей
Route::middleware('auth:api')->group( function () {

    // Выход
    Route::get('/logout', [AuthController::class, 'logout']);

    // Загрузка файлов
    Route::post('/files', [FileController::class, 'store']);

    // Просмотр файлов пользователя
    Route::get('/files/disk', [FileController::class, 'owned']);

    // Просмотр файлов, к которым имеет доступ пользователь
    Route::get('/files/shared', [FileController::class, 'allowed']);

    // Редактирование файла
    Route::patch('/files/{file_id}', [FileController::class, 'edit']);

    // Скачивание файла
    Route::get('/files/{file_id}', [FileController::class, 'download']);

    // Удаление файла
    Route::delete('/files/{file_id}', [FileController::class, 'destroy']);

    // Добавление прав доступа
    Route::post('/files/{file_id}/accesses', [AccessController::class, 'add']);
    // Удаление прав доступа
    Route::delete('/files/{file_id}/accesses', [AccessController::class, 'destroy']);


});
