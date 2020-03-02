<?php

namespace App\Http\Controllers;

class RebuyController extends GameTransactionController
{
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->transaction_type = 'rebuy';
        $this->transaction_relationship = 'rebuys';
    }
}
