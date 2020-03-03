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
        return [
            'start_time' => 'sometimes|nullable|date|before_or_equal:now',
            'stake_id' => 'sometimes|integer|exists:stakes,id',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'table_size_id' => 'sometimes|integer|exists:table_sizes,id',
            'location' => 'sometimes|string',
            'end_time' => 'sometimes|nullable|date|after:start_time',
        ];
    }
}
