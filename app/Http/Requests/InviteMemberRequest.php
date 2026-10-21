<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'student_number' => ['required', 'string', 'max:30', 'unique:members,student_number'],
        ];
    }
}

