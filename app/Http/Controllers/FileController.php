<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileEditRequest;
use App\Http\Requests\FileStoreRequest;
use App\Models\Access;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Загрузка файлов
    public function store(FileStoreRequest $request)
    {
        // Проверка наличия и валидации файлов
        $validatedFiles = [];
        foreach ($request->file('files') as $file) {
            // Валидация файла
            $validator = Validator::make(['file' => $file], [
                'file' => 'required|file|max:2048|mimes:doc,pdf,docx,zip,jpeg,jpg,png',
            ]);

            // Если файл не прошел валидацию, добавляем сообщение об ошибке
            if ($validator->fails()) {
                $errors = $validator->errors()->get('file');
                $validatedFiles[] = [
                    'success' => false,
                    'message' => $errors[0], // Возьмем только первое сообщение об ошибке
                    'name' => $file->getClientOriginalName(),
                ];
            } else {
                // Файл прошел валидацию, загружаем его
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $this->generateUniqueFileName($originalName, $extension);
                $file->storeAs('uploads', $fileName);

                // Создание записи в базе данных
                $uploadedFile = new File();
                $uploadedFile->name = pathinfo($originalName, PATHINFO_FILENAME);
                $uploadedFile->path = $fileName;
                $uploadedFile->file_id = Str::random(10);
                // Добавление связи с пользователем, предполагая, что информация о пользователе доступна в запросе
                $uploadedFile->user_id = auth()->id();
                $uploadedFile->save();

                // Добавляем информацию о успешной загрузке
                $validatedFiles[] = [
                    'success' => true,
                    'code' => 200,
                    'message' => 'Success',
                    'name' => $originalName,
                    'url' => url("files/{$uploadedFile->file_id}"),
                    'file_id' => $uploadedFile->file_id
                ];
            }
        }

        // Возвращаем JSON-ответ с результатами загрузки файлов
        return response()->json($validatedFiles)->setStatusCode(200);

    }

    // Генерация уникального имени файла перед загрузкой файлов
    private function generateUniqueFileName($originalName, $extension)
    {
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $i = 1;

        // Проверка наличия файла с таким же именем
        while (Storage::exists("uploads/{$fileName}.{$extension}")) {
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . " ({$i})";
            $i++;
        }

        return $fileName . '.' . $extension;
    }

    // Редактирование файла
    public function edit(FileEditRequest $request, $file_id)
    {

        // Проверяем, существует ли файл с переданным идентификатором
        $file = File::where('file_id', $file_id)->first();

        // Если файл не найден, возвращаем ответ 404 Not Found
        if (!$file) {
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Проверяем, имеет ли пользователь доступ к файлу
        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            // Если доступ запрещен, возвращаем ответ 403 Forbidden
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        // Обновляем имя файла
        $file->name = $request->input('name');
        $file->save();

        // Возвращаем успешный ответ
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Renamed'
        ]);
    }

    // Скачивание файла
    public function download($file_id)
    {
        // Проверяем, существует ли файл с данным идентификатором
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            // Если файл не найден, возвращаем ответ 404 Not Found
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Проверяем, имеет ли пользователь доступ к файлу
        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            // Если доступ запрещен, возвращаем ответ 403 Forbidden
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        // Получаем путь к файлу на сервере
        $filePath = storage_path('app/uploads/' . $file->path);

        // Проверяем существует ли файл
        if (!Storage::exists('uploads/' . $file->path)) {
            // Если файл не найден на сервере, возвращаем ответ 404 Not Found
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Возвращаем файл для скачивания
        return response()->download($filePath, $file->name . '.' . $file->extension);
    }

    // Удаление
    public function destroy(Request $request, $file_id)
    {
        // Проверяем, существует ли файл с переданным идентификатором
        $file = File::where('file_id', $file_id)->first();

        // Если файл не найден, возвращаем ответ 404 Not Found
        if (!$file) {
            return response()->json(['message' => 'Not found', 'code' => 404], 404);
        }

        // Проверяем, имеет ли пользователь доступ к файлу
        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            // Если доступ запрещен, возвращаем ответ 403 Forbidden
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        // Удаляем файл из хранилища
        Storage::delete('uploads/' . $file->path);

        // Удаляем запись о файле из базы данных
        $file->delete();

        // Возвращаем успешный ответ
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'File deleted'
        ]);
    }


    public function owned(Request $request)
    {
        // Получаем идентификатор текущего авторизованного пользователя
        $userId = $request->user()->id;

        // Получаем файлы, загруженные текущим пользователем
        $files = File::where('user_id', $userId)->with('access.user')->get();

        // Формируем ответ
        $response = [];
        foreach ($files as $file) {
            $accesses = [];
            foreach ($file->access as $right) {
                $accesses[] = [
                    'fullname' => $right->user->full_name,
                    'email' => $right->user->email,
                    'type' => 'co-author',
                ];
            }

            $response[] = [
                'file_id' => $file->file_id,
                'name' => $file->name,
                'code' => 200,
                'url' => url("api-file/files/{$file->file_id}"),
                'accesses' => $accesses
            ];
        }

        // Возвращаем сформированный ответ
        return response()->json($response, 200);
    }

    public function allowed()
    {
        // Получаем идентификатор текущего авторизованного пользователя
        $userId = auth()->id();

        // Получаем файлы, к которым пользователь имеет доступ через таблицу прав доступа
        $filesWithAccess = Access::where('user_id', $userId)->with('file')->get()->pluck('file');

        // Формируем ответ, исключая файлы, загруженные самим пользователем
        $response = [];
        foreach ($filesWithAccess as $file) {
            if ($file->user_id != $userId) {
                $response[] = [
                    'file_id' => $file->file_id,
                    'code' => 200,
                    'name' => $file->name,
                    'url' => url("files/{$file->file_id}")
                ];
            }
        }

        // Возвращаем сформированный ответ
        return response()->json($response, 200);
    }

}
