<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksSubmissionDelay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentFormRequest extends FormRequest
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
            'author_name' => ['required', 'string', 'max:80'],
            'author_email' => ['required', 'email:rfc,dns', 'max:160'],
            'content' => ['required', 'string', 'min:5', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'prohibited'],
            'ts' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'Votre nom est requis.',
            'author_email.required' => 'Votre email est requis.',
            'author_email.email' => 'Cet email n\'est pas valide.',
            'content.required' => 'Votre commentaire est vide.',
            'content.min' => 'Votre commentaire est un peu court.',
            'content.max' => 'Votre commentaire est trop long (5000 caractères maximum).',
            'consent.accepted' => 'Vous devez accepter le traitement de vos données.',
            'website.prohibited' => 'Erreur de soumission.',
        ];
    }
}
