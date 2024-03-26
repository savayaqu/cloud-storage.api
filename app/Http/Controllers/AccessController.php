<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccessController extends Controller
{
    public function add(Request $request, $file_id)
    {
        // Поиск файла по его ID
        $file = File::where('file_id', $file_id)->first();

        // Проверка, существует ли файл
        if (!$file) {
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Проверяем, имеет ли пользователь доступ к файлу
        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            // Если доступ запрещен, возвращаем ответ 403 Forbidden
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        // Валидация входящего запроса
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        // Если валидация не прошла, возвращаем ошибку
        if ($validator->fails()) {
            return response()->json(["success" => false, "code" => 422, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        // Поиск пользователя по email
        $user = User::where('email', $request->email)->first();

        $clone = Access::where('file_id', $file->id)->where('user_id', $user->id)->first();
        if (!$clone) {
            // Создание записи в базе данных для нового права доступа
            $right = new Access();
            $right->file_id = $file->id;
            $right->user_id = $user->id;
            $right->save();
        }


        // Получение всех пользователей, имеющих доступ к файлу
        $rights = Access::where('file_id', $file->id)->with('user')->get();

        // Добавление автора файла
        $author = $file->user;
        $response[] = [
            'fullname' => $author->full_name,
            'email' => $author->email,
            'type' => 'author',
            'code' => 200,
        ];

        // Добавление соавторов файла
        foreach ($rights as $access) {
            $user = $access->user;
            $response[] = [
                'fullname' => $user->full_name,
                'email' => $user->email,
                'type' => 'co-author',
                'code' => 200,
            ];
        }

        return response()->json($response, 200);
    }

    public function destroy(Request $request, $file_id)
    {
        // Поиск файла по его ID
        $file = File::where('file_id', $file_id)->first();

        // Проверка, существует ли файл
        if (!$file) {
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Проверяем, имеет ли пользователь доступ к файлу
        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            // Если доступ запрещен, возвращаем ответ 403 Forbidden
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        // Валидация входящего запроса
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        // Если валидация не прошла, возвращаем ошибку
        if ($validator->fails()) {
            return response()->json(["success" => false, "code" => 422, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        // Находим пользователя, которого нужно удалить из списка соавторов
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Удаляем запись о праве доступа к файлу для указанного пользователя
        $right = Access::where('file_id', $file->id)->where('user_id', $user->id)->first();

        if ($right) {
            $right->delete();
        }
        // Получение всех пользователей, имеющих доступ к файлу
        $rights = Access::where('file_id', $file->id)->with('user')->get();

        // Добавление автора файла
        $author = $file->user;
        $response[] = [
            'fullname' => $author->full_name,
            'email' => $author->email,
            'type' => 'author',
            'code' => 200,
        ];

        // Добавление соавторов файла
        foreach ($rights as $access) {
            $user = $access->user;
            $response[] = [
                'fullname' => $user->full_name,
                'email' => $user->email,
                'type' => 'co-author',
                'code' => 200,
            ];
        }

        return response()->json($response);
    }
}
