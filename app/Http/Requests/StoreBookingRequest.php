<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'flight_from' => ['required', 'array'],
            'flight_from.id' => ['required', Rule::exists('flights', 'id')],
            'flight_from.date' => ['required', 'date_format:Y-m-d'],

            'flight_back' => ['required', 'array'],
            'flight_back.id' => ['required', Rule::exists('flights', 'id')],
            'flight_back.date' => ['required', 'date_format:Y-m-d'],

            'passengers' => ['required', 'array'],
            'passengers.*.first_name' => ['required', 'string'],
            'passengers.*.last_name' => ['required', 'string'],
            'passengers.*.birth_date' => ['required', 'date_format:Y-m-d'],
            'passengers.*.document_number' => ['required', 'digits:10'],
        ];
    }
}
