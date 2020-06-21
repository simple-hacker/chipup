<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Money\Currency;
use Money\Currencies\ISOCurrencies;

class CurrencyRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Create a Currency based on the string provided by user.

        $currencies = new ISOCurrencies();
        $currency = new Currency($value);

        // If list of currencies contains user generated currency, return true
        return $currencies->contains($currency);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute is not a supported currency.';
    }
}
