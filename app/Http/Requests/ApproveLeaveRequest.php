<?php

namespace App\Http\Requests;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;

class ApproveLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === Roles::Manager->value;
    }

    public function rules(): array
    {
        return [
            'status_1' => 'required|string|in:approved,rejected',
            'note_1' => 'nullable|string|min:3|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status_1.required' => 'Status wajib diisi.',
            'status_1.in' => 'Status hanya boleh berisi: approved atau rejected.',
            'note_1.string' => 'Catatan 1 harus berupa teks.',
            'note_1.min' => 'Catatan 1 minimal harus berisi 3 karakter.',
            'note_1.max' => 'Catatan 1 maksimal hanya boleh 100 karakter.',
        ];
    }
}
