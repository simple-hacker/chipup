<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'location' => 'sometimes|string',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'position' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
            'comments' => 'sometimes|nullable|string',
            'end_time' => 'sometimes|nullable|date|before_or_equal:now',
        ];

        // If both start_time and end_time are present then start_time must be before end_time
        // This is because both fields are sometimes supplied and if validated against null the validation fails
        if ($this->input('start_time') && $this->input('end_time')) {
            $rules['start_time'] .= '|before_or_equal:end_time';
        }

        return $rules;
    }
}
