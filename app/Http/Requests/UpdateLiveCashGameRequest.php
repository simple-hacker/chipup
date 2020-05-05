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
            'end_time' => 'sometimes|nullable|date|before_or_equal:now',
        ];

        // If both start_time and end_time are present then start_time must be before end_time
        // This is because both fields are sometimes supplied and if validated against null the validation fails
        if ($this->input('start_time') && $this->input('end_time')) {
            $rules['start_time'] .= '|before:end_time';
        }

        return $rules;
    }
}
