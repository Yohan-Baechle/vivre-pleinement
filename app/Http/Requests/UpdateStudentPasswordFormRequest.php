<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateStudentPasswordFormRequest extends FormRequest
{
    /**
     * Sac d'erreurs dédié : la page « Mon compte » porte trois formulaires dont
     * deux exposent un champ `current_password`.
     *
     * @var string
     */
    protected $errorBag = 'updatePassword';

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:student'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
