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
            'location' => 'sometimes|string',
            'stake_id' => 'sometimes|integer|exists:stakes,id',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'table_size_id' => 'sometimes|integer|exists:table_sizes,id',
            'comments' => 'sometimes|nullable|string',
            'start_time' => 'sometimes|date|before_or_equal:now',
            'end_time' => 'sometimes|nullable|date|before_or_equal:now',
        ];

        // If both start_time and end_time are present then end_time must be after start_time
        // This is because both fields are sometimes supplied and if validated against null the validation fails
        if ($this->input('start_time') && $this->input('end_time')) {
            $rules['end_time'] .= '|after_or_equal:start_time';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'location.string' => 'Location must be text.',

            'stake_id.*' => 'Please choose a stake.',
            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',
            'table_size_id.*' => 'Please choose a ring type.',

            'comment.string' => 'Comments must be text.',

            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',

            'end_time.date' => 'Please select a valid date.',
            'end_time.before_or_equal' => 'End time cannot be in the future.',
            'end_time.after_or_equal' => 'End time cannot be before start time.',
        ];
    }
}
