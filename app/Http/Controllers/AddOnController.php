<?php

namespace App\Http\Controllers;

class AddOnController extends GameTransactionController
{
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->transaction_type = 'addOn';
        $this->transaction_relationship = 'addOns';
    }
}
