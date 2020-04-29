<?php

namespace App\Http\Requests;

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
            'amount' => 'required|numeric',
            'comments' => 'sometimes|nullable|string'
        ];
    }
}
