<?php

namespace App\Http\Requests;

use App\Support\PakistanCities;
use App\Support\Projects;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // From the admin's Stages and Property types; asked only while those lists have entries
            'stage' => [Rule::requiredIf(Projects::stages() !== []), 'nullable', 'string', Rule::in(array_keys(Projects::stages()))],
            'type' => [Rule::requiredIf(Projects::types() !== []), 'nullable', 'string', Rule::in(array_keys(Projects::types()))],
            // Any city in Pakistan: a builder may launch where no project is listed yet
            'city' => ['required', 'string', Rule::in(array_keys(PakistanCities::all()))],
            'company' => ['required', 'string', 'max:120'],
            'project' => ['nullable', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,18}$/'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['prohibited'], // honeypot — humans never see this field
        ];
    }

    public function messages(): array
    {
        return [
            'stage.required' => 'Please choose the project stage.',
            'type.required' => 'Please choose the project type.',
            'city.required' => 'Please choose the city.',
            'company.required' => 'Please enter your company name.',
            'name.required' => 'Please enter your name.',
            'phone.required' => 'Please enter your phone number.',
            'phone.regex' => 'Please enter a valid phone number, e.g. 0300 1234567.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
