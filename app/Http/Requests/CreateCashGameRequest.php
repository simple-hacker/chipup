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
        return [
            'cash_game.start_time' => 'required|date|before_or_equal:now',
            'cash_game.stake_id' => 'required|integer|exists:stakes,id',
            'cash_game.variant_id' => 'required|integer|exists:variants,id',
            'cash_game.limit_id' => 'required|integer|exists:limits,id',
            'cash_game.table_size_id' => 'required|integer|exists:table_sizes,id',
            'cash_game.location' => 'required|string',

            'buy_ins.*.amount' => 'required|integer|min:0',

            'expenses.*.amount' => 'required|integer|min:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'cash_out.end_time' =>'required|date|after_or_equal:start_time',
            'cash_out.amount' => 'required|integer|min:0',
        ];
    }
}
