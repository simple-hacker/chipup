<?php

namespace App\Http\Controllers;

class CashOutController extends GameTransactionController
{
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->transaction_type = 'cashOut';
        $this->transaction_relationship = 'cashOutModel';
    }
}
