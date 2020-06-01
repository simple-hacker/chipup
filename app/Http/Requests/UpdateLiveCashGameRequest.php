<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiveCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'start_time' => 'sometimes|date|before_or_equal:now',
            'stake_id' => 'sometimes|integer|exists:stakes,id',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'table_size_id' => 'sometimes|integer|exists:table_sizes,id',
            'location' => 'sometimes|string',
            'comments' => 'sometimes|nullable|string',
        ];

        return $rules;
    }
}
