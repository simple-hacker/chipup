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
            'start_time' => 'sometimes|date|before_or_equal:now',
            'name' => 'sometimes|nullable|string',
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'limit_id' => 'sometimes|integer|exists:limits,id',
            'location' => 'sometimes|string',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'position' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
            'comments' => 'sometimes|nullable|string',
        ];

        return $rules;
    }
}
