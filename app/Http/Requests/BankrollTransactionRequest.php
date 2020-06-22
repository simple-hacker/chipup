<?php

namespace App\Http\Requests;

use App\Rules\CurrencyRule;
use Illuminate\Foundation\Http\FormRequest;

class BankrollTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => 'sometimes|date|before:tomorrow',
            'amount' => 'required|numeric|not_in:0',
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
            'date.date' => 'Please enter a valid date.',
            'date.before' => 'Date cannot be in the future.',
            'amount.required' => 'Please enter a valid amount.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.not_in' => 'Amount cannot be zero.',
            'comments.string' => 'Comments must be text.'
        ];
    }
}
