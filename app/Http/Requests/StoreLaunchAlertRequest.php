<?php

namespace App\Http\Requests;

use App\Support\Projects;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaunchAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,18}$/'],
            'city' => ['nullable', 'string', Rule::in(array_keys(Projects::cities() + Projects::liveCities()))],
            'website' => ['prohibited'], // honeypot — humans never see this field
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Please enter your phone number.',
            'phone.regex' => 'Please enter a valid phone number, e.g. 0300 1234567.',
        ];
    }
}
