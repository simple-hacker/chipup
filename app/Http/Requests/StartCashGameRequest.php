<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // TODO: Validate, start_time cannot be during another session that exists.

        return [
            'start_time' => 'sometimes|nullable|date|before_or_equal:now',
            'amount' => 'required|numeric|min:0',
            'stake_id' => 'required|integer|exists:stakes,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'limit_id' => 'required|integer|exists:limits,id',
            'table_size_id' => 'required|integer|exists:table_sizes,id',
            'location' => 'required|string'
        ];
    }
}
