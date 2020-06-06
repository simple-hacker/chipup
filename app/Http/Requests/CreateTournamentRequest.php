<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTournamentRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string',
            'limit_id' => 'required|integer|exists:limits,id',
            'variant_id' => 'required|integer|exists:variants,id',
            'prize_pool' => 'sometimes|nullable|integer|min:0',
            'position' => 'sometimes|nullable|integer|min:0',
            'entries' => 'sometimes|nullable|integer|min:0',
            'start_time' => 'required|date|before_or_equal:now',
            'end_time' =>'required|date|before_or_equal:now',
            'comments' => 'sometimes|nullable|string',

            'buy_in.amount' => 'sometimes|numeric|min:0',

            'expenses.*.amount' => 'required_with:expenses.*.comments|numeric|min:0|not_in:0',
            'expenses.*.comments' => 'sometimes|nullable|string',

            'rebuys.*.amount' => 'sometimes|numeric|min:0|not_in:0',

            'add_ons.*.amount' => 'sometimes|numeric|min:0|not_in:0',

            'cash_out.amount' => 'sometimes|numeric|min:0',
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
            'name.string' => 'Tournament name must be text.',
            'limit_id.*' => 'Please choose a game limit.',
            'variant_id.*' => 'Please choose a game type.',
            'prize_pool.*' => 'Prize pool must be a positive amount',
            'position.*' => 'Prize pool must be a positive number',
            'entries.*' => 'Prize pool must be a positive number',
            'start_time.required' => 'Please select a date.',
            'start_time.date' => 'Please select a valid date.',
            'start_time.before_or_equal' => 'Start time cannot be in the future.',
            'end_time.required' => 'Please select a date.',
            'end_time.date' => 'Please select a valid date.',
            'end_time.before_or_equal' => 'End time cannot be in the future.',
            'end_time.after_or_equal' => 'End time cannot be before start time.',
            'comments.string' => 'Comments must be text.',

            'buy_in.amount.numeric' => 'Buy in amount must be a number.',
            'buy_in.amount.min' => 'Buy in must be a positive amount',

            'expenses.*.amount.required_with' => 'Please enter an expense amount.',
            'expenses.*.amount.numeric' => 'Expense amount must be a number.',
            'expenses.*.amount.min' => 'Expense must be a positive amount',
            'expenses.*.amount.not_in' => 'Expense amount cannot be zero',
            'expenses.*.comments.string' => 'Comments must be text.',

            'rebuys.*.amount.numeric' => 'Rebuy amount must be a number.',
            'rebuys.*.amount.min' => 'Rebuy must be a positive amount',
            'rebuys.*.amount.not_in' => 'Rebuy amount cannot be zero',

            'add_ons.*.amount.numeric' => 'Add on amount must be a number.',
            'add_ons.*.amount.min' => 'Add on must be a positive amount',
            'add_ons.*.amount.not_in' => 'Add on amount cannot be zero',

            'cash_out.amount.numeric' => 'Cash out amount must be a number.',
            'cash_out.amount.min' => 'Cash out must be a positive amount',
        ];
    }
}
