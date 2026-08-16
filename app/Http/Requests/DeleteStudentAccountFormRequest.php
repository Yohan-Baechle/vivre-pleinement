<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteStudentAccountFormRequest extends FormRequest
{
    /**
     * Sac d'erreurs dédié : la page « Mon compte » porte trois formulaires dont
     * deux exposent un champ `current_password`.
     *
     * @var string
     */
    protected $errorBag = 'deleteAccount';

    /**
     * L'anonymisation est irréversible : on exige le mot de passe courant pour
     * qu'une session volée ou laissée ouverte ne suffise pas à détruire le
     * compte.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:student'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Saisissez votre mot de passe pour confirmer la suppression.',
            'current_password.current_password' => 'Ce mot de passe est incorrect.',
        ];
    }
}
