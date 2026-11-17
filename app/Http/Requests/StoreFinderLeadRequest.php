<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinderLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,18}$/'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:120'],
            // answers[question id][] = option label; checked against the live questions in the controller
            'answers' => ['nullable', 'array', 'max:20'],
            'answers.*' => ['array', 'max:50'],
            'answers.*.*' => ['string', 'max:160'],
            'website' => ['prohibited'], // honeypot: humans never see this field
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'phone.required' => 'Please enter your phone number.',
            'phone.regex' => 'Please enter a valid phone number, e.g. 0300 1234567.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
