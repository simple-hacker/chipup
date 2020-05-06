<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'cash_game.start_time' => 'sometimes|date|before_or_equal:now',
            'cash_game.stake_id' => 'sometimes|integer|exists:stakes,id',
            'cash_game.variant_id' => 'sometimes|integer|exists:variants,id',
            'cash_game.limit_id' => 'sometimes|integer|exists:limits,id',
            'cash_game.table_size_id' => 'sometimes|integer|exists:table_sizes,id',
            'cash_game.location' => 'sometimes|string',
            'cash_game.comments' => 'sometimes|nullable|string',
            'cash_game.end_time' => 'sometimes|nullable|date|before_or_equal:now',

            'buy_ins.*.id' => 'sometimes|exists:buy_ins',
            'buy_ins.*.amount' => 'required|numeric|min:0',

            'expenses.*.id' => 'sometimes|exists:expenses',
            'expenses.*.amount' => 'required|numeric|min:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'cash_out.amount' => 'sometimes|numeric|min:0'
        ];

        // If both start_time and end_time are present then start_time must be before end_time
        // This is because both fields are sometimes supplied and if validated against null the validation fails
        if ($this->input('cash_game.start_time') && $this->input('cash_game.end_time')) {
            $rules['cash_game.start_time'] .= '|before:end_time';
        }

        return $rules;
    }
}
