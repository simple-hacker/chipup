<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'start_time' => 'required|date|before_or_equal:now',
            'stake_id' => 'required|integer|exists:stakes,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'limit_id' => 'required|integer|exists:limits,id',
            'table_size_id' => 'required|integer|exists:table_sizes,id',
            'location' => 'required|string',
            'end_time' =>'required|date|before_or_equal:now',
            'comments' => 'nullable|string',

            'buy_ins.*.amount' => 'required|numeric|min:0',

            'expenses.*.amount' => 'required_with:expenses.*.comments|numeric|min:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'cash_out_model.amount' => 'sometimes|numeric|min:0',
        ];

        if ($this->input('start_time') && $this->input('end_time')) {
            $rules['end_time'] .= '|after:start_time';
        }

        return $rules;
    }
}
