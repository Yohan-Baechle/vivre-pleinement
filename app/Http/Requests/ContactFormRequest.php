<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksSubmissionDelay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactFormRequest extends FormRequest
{
    use ChecksSubmissionDelay;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', Rule::in(['rdv', 'question', 'partenariat', 'media', 'autre'])],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'prohibited'],
            'ts' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Votre prénom est requis.',
            'email.required' => 'Votre email est requis.',
            'email.email' => 'Cet email n\'est pas valide.',
            'subject.required' => 'Choisissez l\'objet de votre message.',
            'subject.in' => 'Objet invalide.',
            'message.required' => 'Votre message est requis.',
            'message.min' => 'Votre message est un peu court (20 caractères minimum).',
            'message.max' => 'Votre message est trop long (5000 caractères maximum).',
            'consent.accepted' => 'Vous devez accepter le traitement de vos données pour envoyer le message.',
            'website.prohibited' => 'Erreur de soumission.',
        ];
    }

    public function subjectLabel(): string
    {
        return match ($this->input('subject')) {
            'rdv' => 'Prise de rendez-vous',
            'question' => 'Question sur l\'accompagnement',
            'partenariat' => 'Partenariat',
            'media' => 'Demande presse / média',
            default => 'Autre',
        };
    }
}
