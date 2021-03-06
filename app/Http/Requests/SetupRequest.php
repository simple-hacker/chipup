<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use App\Rules\LocaleRule;
use Illuminate\Foundation\Http\FormRequest;

class SetupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bankroll' => 'sometimes|numeric|min:0',
            'locale' => ['sometimes', 'string', new LocaleRule],
            'currency' => ['bail', 'sometimes', 'string', new CurrencyRule],
            'default_stake_id' => 'sometimes|nullable|integer|exists:stakes,id',
            'default_limit_id' => 'sometimes|nullable|integer|exists:limits,id',
            'default_variant_id' => 'sometimes|nullable|integer|exists:variants,id',
            'default_table_size_id' => 'sometimes|nullable|integer|exists:table_sizes,id',
            'default_location' => 'sometimes|nullable|string',
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
            'bankroll.numeric' => 'Amount must be a number.',
            'bankroll.min' => 'Amount must be a positive number.',

            'default_stake_id.*' => 'Please select a stake.',
            'default_limit_id.*' => 'Please select a game limit.',
            'default_variant_id.*' => 'Please select a game variant.',
            'default_table_size_id.*' => 'Please select a ring size.',

            'default_location.string' => 'Location must be text.',
        ];
    }
}
