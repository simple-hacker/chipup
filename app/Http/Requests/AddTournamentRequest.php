<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTournamentRequest extends FormRequest
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
            'amount' => 'required|integer|min:0',
            'name' => 'sometimes|nullable|string',
            'limit_id' => 'required|integer|exists:limits,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'entries' => 'sometimes|nullable|integer|min:0',
            'location' => 'required|string'
        ];
    }
}
