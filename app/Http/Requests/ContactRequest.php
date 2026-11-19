<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public const TOPICS = ['General enquiry', 'ImageSafe', 'Data protection & security', 'Billing & licences', 'Press'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'org' => ['nullable', 'string', 'max:190'],
            'topic' => ['required', Rule::in(self::TOPICS)],
            'message' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string'], // honeypot: real visitors leave it empty
        ];
    }
}
