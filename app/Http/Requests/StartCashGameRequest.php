<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;

class StartCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location' => 'required|string',
            'currency' => ['sometimes', 'string', new CurrencyRule],
            'amount' => 'required|numeric|min:0|not_in:0',
            'stake_id' => 'required|integer|exists:stakes,id',
            'limit_id' => 'required|integer|exists:limits,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'table_size_id' => 'required|integer|exists:table_sizes,id',
            'start_time' => 'sometimes|nullable|date|before_or_equal:now',
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
            'location.required' => 'Please enter a location.',
            'location.string' => 'Location must be text.',

            'amount.required' => 'Please enter a buy in amount.',
            'amount.numeric' => 'Buy in amount must be a number.',
            'amount.min' => 'Buy in must be a positive amount',
            'amount.not_in' => 'Buy in amount cannot be zero',

            'stake_id.*' => 'Please choose a stake.',
            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',
            'table_size_id.*' => 'Please choose a ring type.',

            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',
        ];
    }
}
