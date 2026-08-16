<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentProfileFormRequest extends FormRequest
{
    /**
     * Le mot de passe courant n'est exigé que si l'adresse e-mail change :
     * c'est elle qui commande la réinitialisation du mot de passe, donc pouvoir
     * la modifier depuis une session volée suffirait à prendre le compte.
     * Changer seulement son nom ne demande rien.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('students', 'email')->ignore($this->user('student')?->id)],
            'current_password' => Rule::when(
                $this->emailIsChanging(),
                ['required', 'current_password:student'],
                ['nullable'],
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Saisissez votre mot de passe actuel pour changer d\'adresse e-mail.',
            'current_password.current_password' => 'Ce mot de passe est incorrect.',
        ];
    }

    private function emailIsChanging(): bool
    {
        return $this->string('email')->trim()->value() !== $this->user('student')?->email;
    }
}
