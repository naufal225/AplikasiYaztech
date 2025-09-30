<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\Roles;

class ApproveOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Approver atau Manager yang aktif yang boleh memproses approval
        return Auth::check() && (
            Auth::user()->hasActiveRole(Roles::Approver->value)
            || Auth::user()->hasActiveRole(Roles::Manager->value)
        );
    }

    public function rules(): array
    {
        return [
            'status_1' => 'required|string|in:approved,rejected',
            'note_1' => 'nullable|string|min:3|max:100',
            'status_2' => 'required|string|in:approved,rejected',
            'note_2' => 'nullable|string|min:3|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status_1.required' => 'Status wajib diisi.',
            'status_1.in' => 'Status hanya boleh approved/rejected.',
            'status_2.required' => 'Status wajib diisi.',
            'status_2.in' => 'Status hanya boleh approved/rejected.',
            'note_1.string' => 'Catatan harus berupa teks.',
            'note_1.min' => 'Catatan minimal 3 karakter.',
            'note_1.max' => 'Catatan maksimal 100 karakter.',
            'note_2.string' => 'Catatan harus berupa teks.',
            'note_2.min' => 'Catatan minimal 3 karakter.',
            'note_2.max' => 'Catatan maksimal 100 karakter.',
        ];
    }
}
