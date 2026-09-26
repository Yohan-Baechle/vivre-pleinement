<?php

namespace App\Http\Requests\Automation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVideoEnrichmentFormRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'intro' => ['required', 'string', 'max:5000'],
            'summary' => ['required', 'string', 'max:1000'],
            'seo_description' => ['required', 'string', 'max:320'],
            'key_takeaways' => ['required', 'array', 'min:1', 'max:12'],
            'key_takeaways.*.title' => ['required', 'string', 'max:200'],
            'key_takeaways.*.content' => ['required', 'string', 'max:1000'],
            'chapters' => ['present', 'array'],
            'chapters.*.title' => ['required', 'string', 'max:200'],
            'chapters.*.start_seconds' => ['required', 'integer', 'min:0'],
            'category_slugs' => ['present', 'array'],
            'category_slugs.*' => ['string', 'max:120'],
        ];
    }
}
