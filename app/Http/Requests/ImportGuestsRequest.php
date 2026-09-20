<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportGuestsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if (! $file) {
                return;
            }

            $allowedNames = [
                'plantilla_invitados.csv',
                'plantilla_invitados.xls',
                'plantilla_invitados.xlsx',
            ];

            $originalName = strtolower(trim((string) $file->getClientOriginalName()));
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mime = strtolower((string) $file->getMimeType());
            $allowedExtensions = ['csv', 'xls', 'xlsx'];
            $allowedMimeTypes = [
                'text/csv',
                'application/csv',
                'application/vnd.ms-excel',
                'application/octet-stream',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel.sheet.macroenabled.12',
            ];

            $isAllowedType = in_array($extension, $allowedExtensions, true)
                || in_array($mime, $allowedMimeTypes, true);

            if (! $isAllowedType) {
                $validator->errors()->add('file', 'El archivo debe ser de tipo: csv, xlsx o xls.');
                return;
            }

            if (! in_array($originalName, $allowedNames, true)) {
                $validator->errors()->add('file', 'El nombre del archivo es incorrecto. Debe llamarse exactamente: plantilla_invitados.csv, plantilla_invitados.xls o plantilla_invitados.xlsx');
            }
        });
    }
}
