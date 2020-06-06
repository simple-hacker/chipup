<?php

namespace App\Http\Controllers;

class BuyInController extends GameTransactionController
{ 
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->transaction_type = 'buyIn';
    }
}
