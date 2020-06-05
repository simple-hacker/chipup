<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCashGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'location' => 'required|string',
            'stake_id' => 'required|integer|exists:stakes,id',
            'limit_id' => 'required|integer|exists:limits,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'table_size_id' => 'required|integer|exists:table_sizes,id',
            'start_time' => 'required|date|before_or_equal:now',
            'end_time' =>'required|date|before_or_equal:now',
            'comments' => 'nullable|string',

            'buy_ins.*.amount' => 'required|numeric|min:0|not_in:0',

            'expenses.*.amount' => 'required_with:expenses.*.comments|numeric|min:0|not_in:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'cash_out_model.amount' => 'sometimes|numeric|min:0',
        ];

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
            'location.required' => 'Please enter a location.',
            'location.string' => 'Location must be text.',
            'stake_id.*' => 'Please choose a stake.',
            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',
            'table_size_id.*' => 'Please choose a ring type.',
            'start_time.required' => 'Please select a date.',
            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',
            'end_time.required' => 'Please select a date.',
            'end_time.date' => 'Please select a valid date.',
            'end_time.before_or_equal' => 'End time cannot be in the future.',
            'end_time.after_or_equal' => 'End time cannot be before start time.',
            'comments.string' => 'Comments must be text.',
            
            'buy_ins.*.amount.required' => 'Please enter a buy in amount.',
            'buy_ins.*.amount.numeric' => 'Buy in amount must be a number.',
            'buy_ins.*.amount.min' => 'Buy in must be a positive amount',
            'buy_ins.*.amount.not_in' => 'Buy in amount cannot be zero',

            'expenses.*.amount.required_with' => 'Please enter an expense amount.',
            'expenses.*.amount.numeric' => 'Expense amount must be a number.',
            'expenses.*.amount.min' => 'Expense must be a positive amount',
            'expenses.*.amount.not_in' => 'Expense amount cannot be zero',            
            'expenses.*.comments.string' => 'Comments must be text.',

            'cash_out_model.amount.numeric' => 'Cash out amount must be a number.',
            'cash_out_model.amount.min' => 'Cash out must be a positive amount',
        ];
    }
}