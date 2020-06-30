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
            'straddle_1' => 'sometimes|nullable|required_with_all:straddle_2,straddle_3|numeric|min:0|not_in:0|gte:big_blind',
            'straddle_2' => 'sometimes|nullable|required_with:straddle_3|numeric|min:0|not_in:0|gte:straddle_1',
            'straddle_3' => 'sometimes|nullable|numeric|min:0|not_in:0|gte:straddle_2',
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
            'small_blind.numeric' => 'Small blind must be a number.',
            'small_blind.min' => 'Small blind must be a positive number.',
            'small_blind.not_in' => 'Small blind cannot be zero',

            'big_blind.numeric' => 'Big blind must be a number.',
            'big_blind.min' => 'Big blind must be a positive number.',
            'big_blind.not_in' => 'Big blind cannot be zero',
            'big_blind.gte' => 'Big blind must be greater than the small blind.',

            'straddle_1.numeric' => 'Straddle must be a number.',
            'straddle_1.min' => 'Straddle must be a positive number.',
            'straddle_1.not_in' => 'Straddle cannot be zero',
            'straddle_1.gte' => 'Straddle must be greater than the big blind.',

            'straddle_2.numeric' => 'Straddle must be a number.',
            'straddle_2.min' => 'Straddle must be a positive number.',
            'straddle_2.not_in' => 'Straddle cannot be zero',
            'straddle_2.gte' => 'Straddle must be greater than the previous straddle.',

            'straddle_2.numeric' => 'Straddle must be a number.',
            'straddle_2.min' => 'Straddle must be a positive number.',
            'straddle_2.not_in' => 'Straddle cannot be zero',
            'straddle_2.gte' => 'Straddle must be greater than the previous straddle.',
        ];
    }
}
