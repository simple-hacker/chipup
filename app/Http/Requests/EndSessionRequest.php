<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;

class EndSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'end_time' => 'sometimes|date|before_or_equal:now',
            'currency' => ['sometimes', 'string', new CurrencyRule],
            'amount' => 'sometimes|numeric|min:0',
            'position' => 'sometimes|nullable|integer|min:0|not_in:0',
            'entries' => 'sometimes|nullable|integer|min:0|not_in:0'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'end_time.date' => 'Please enter a valid end time.',
            'end_time.before_or_equal' => 'End time cannot be in the future.',

            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be a positive number.',
        ];
    }
}
