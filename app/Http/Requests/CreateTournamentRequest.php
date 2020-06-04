<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTournamentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'location' => 'required|string',
            'name' => 'sometimes|nullable|string',
            'variant_id' => 'required|integer|exists:variants,id',
            'limit_id' => 'required|integer|exists:limits,id',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'position' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
            'start_time' => 'required|date|before_or_equal:now',
            'end_time' =>'required|date|before_or_equal:now',
            'comments' => 'sometimes|nullable|string',

            'buy_in.amount' => 'required|numeric|min:0',

            'expenses.*.amount' => 'required_with:expenses.*.comments|numeric|min:0|not_in:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'rebuys.*.amount' => 'sometimes|numeric|min:0|not_in:0',

            'add_ons.*.amount' => 'sometimes|numeric|min:0|not_in:0',

            'cash_out_model.amount' => 'sometimes|numeric|min:0',
        ];

        if ($this->input('start_time') && $this->input('end_time')) {
            $rules['end_time'] .= '|after_or_equal:start_time';
        }
    
        return $rules;
    }
}
