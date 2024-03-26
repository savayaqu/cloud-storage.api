<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileStoreRequest extends ApiRequest
{
    // Правила валидации при загрузки файлов
    public function rules(): array
    {
        return [
            'files' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'files.*.required' => 'Each file is required.',
            'files.*.file' => 'Each file must be a valid file.',
            'files.*.max' => 'Each file may not be greater than 2048 kilobytes.',
            'files.*.mimes' => 'Each file must be of type: doc, pdf, docx, zip, jpeg, jpg, png.',
        ];
    }
}
