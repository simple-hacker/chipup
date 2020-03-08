<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bankroll' => 'sometimes|integer|min:0',
            'default_stake_id' => 'sometimes|nullable|integer|exists:stakes,id',
            'default_limit_id' => 'sometimes|nullable|integer|exists:limits,id',
            'default_variant_id' => 'sometimes|nullable|integer|exists:variants,id',
            'default_table_size_id' => 'sometimes|nullable|integer|exists:table_sizes,id',
            'default_location' => 'sometimes|nullable|string',
        ];
    }
}
