<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiveTournamentRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'position' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
            'comments' => 'sometimes|nullable|string',
            'start_time' => 'sometimes|date|before_or_equal:now',
        ];

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
            'name.string' => 'Tournament name must be text.',

            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',

            'prize_pool.*' => 'Prize pool amount must be a positive number.',
            'position.*' => 'Finishing position must be a positive number.',
            'entries.*' => 'Number of entries must be a positive number.',

            'comment.string' => 'Comments must be text.',

            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',
        ];
    }
}
