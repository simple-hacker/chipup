<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartTournamentRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string',
            'currency' => ['sometimes', 'string', new CurrencyRule],
            'amount' => 'sometimes|numeric|min:0',
            'limit_id' => 'required|integer|exists:limits,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
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
            'name.string' => 'Tournament name must be text.',

            'amount.numeric' => 'Buy in amount must be a number.',
            'amount.min' => 'Buy in must be a positive amount',

            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',

            'prize_pool.*' => 'Prize pool amount must be a positive number.',
            'entries.*' => 'Number of entries must be a positive number.',

            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',
        ];
    }
}
