<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateGameTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id' => 'required|integer',
            'game_type' => 'required|string',
            'currency' => ['sometimes', 'string', new CurrencyRule],
            'amount' => 'required|numeric|min:0|not_in:0',
            'comments' => 'sometimes|nullable|string'
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
            'game_id.*' => 'Invalid game id.',
            'game_type.*' => 'Invalid game type.',

            'amount.required' => 'Please enter a valid amount.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be a positive number.',
            'amount.not_in' => 'Amount cannot be zero.',
            'comments.string' => 'Comments must be text.'
        ];
    }
}
