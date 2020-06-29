<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StakeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'small_blind' => 'required|numeric|min:0|not_in:0',
            'big_blind' => 'required|numeric|min:0|not_in:0|gte:small_blind',
            'straddle_1' => 'sometimes|required_with_all:straddle_2,straddle_3|min:0|not_in:0|gte:big_blind',
            'straddle_2' => 'sometimes|required_with:straddle_3|min:0|not_in:0|gte:straddle_1',
            'straddle_3' => 'sometimes|min:0|not_in:0|gte:straddle_2',
        ];
    }
}
