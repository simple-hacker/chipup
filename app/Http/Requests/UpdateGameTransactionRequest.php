<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGameTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'sometimes|numeric|min:0|not_in:0',
            'currency' => ['sometimes', 'string', new CurrencyRule],
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
            'amount.required' => 'Please enter a valid amount.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be a positive number.',
            'amount.not_in' => 'Amount cannot be zero.',
            'comments.string' => 'Comments must be text.'
        ];
    }
}
