<?php

namespace App\Http\Requests;

use App\Support\ContactLocations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    /** What the message is about */
    public const TOPICS = [
        'buying' => 'Buying a property',
        'site-visit' => 'Booking a site visit',
        'partner' => 'Builder partnership',
        'other' => 'Something else',
    ];

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
            'topic' => ['required', 'string', Rule::in(array_keys(self::TOPICS))],
            // City and area are required (C-06): a city with live projects, then one of its areas or "Not sure yet"
            'city' => ['required', 'string', Rule::in(array_keys(ContactLocations::all()))],
            'area' => ['required', 'string', Rule::in([...ContactLocations::areaSlugsFor($this->input('city')), ContactLocations::NOT_SURE])],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['prohibited'], // honeypot — humans never see this field
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'phone.required' => 'Please enter your phone number.',
            'city.required' => 'Please choose a city.',
            'city.in' => 'Please choose a city from the list.',
            'area.required' => 'Please choose an area.',
            'area.in' => 'Please choose an area in this city.',
            'phone.regex' => 'Please enter a valid phone number, e.g. 0300 1234567.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
