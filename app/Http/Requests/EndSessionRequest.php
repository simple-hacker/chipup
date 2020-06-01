<?php

namespace App\Http\Requests;

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
            'end_time' => 'nullable|date|before_or_equal:now',
            'amount' => 'required|numeric|min:0'
        ];
    }
}
