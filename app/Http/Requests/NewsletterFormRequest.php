<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksSubmissionDelay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NewsletterFormRequest extends FormRequest
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
            'email' => ['required', 'email:rfc,dns', 'max:160'],
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
            'website.prohibited' => 'Erreur de soumission.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('home').'#capture';
    }
}
