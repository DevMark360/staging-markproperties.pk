<?php

namespace App\Http\Requests;

use App\Support\Projects;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /** What the visitor is asking for. */
    public const INTERESTS = [
        // No price or payment-plan option: the site never offers or mentions prices
        'general' => 'General enquiry',
        'site-visit' => 'Site visit',
        'brochure' => 'Project brochure',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project' => ['required', 'string', Rule::in(Projects::all()->pluck('slug'))],
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,18}$/'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:120'],
            'interest' => ['required', 'string', Rule::in(array_keys(self::INTERESTS))],
            'message' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'], // honeypot — humans never see this field
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
