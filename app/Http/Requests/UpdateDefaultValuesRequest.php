<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDefaultValuesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'default_stake_id' => 'sometimes|integer|exists:stakes,id',
            'default_limit_id' => 'sometimes|integer|exists:limits,id',
            'default_variant_id' => 'sometimes|integer|exists:variants,id',
            'default_table_size_id' => 'sometimes|integer|exists:table_sizes,id',
            'default_location' => 'sometimes|nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [           
            'default_stake_id.*' => 'Please choose a stake.',
            'default_limit_id.*' => 'Please choose a game limit.',
            'default_variant_id.*' => 'Please choose a game type.',
            'default_table_size_id.*' => 'Please choose a ring type.',
            
            'default_location.string' => 'Location must be text.',
        ];
    }
}
