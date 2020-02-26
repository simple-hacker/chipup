<?php

namespace App;

use App\Abstracts\Game;

class CashGame extends Game
{
    protected $guarded = [];

    /**
    * Extends parent's deleteGameTransactions to include Tournament relationships.
    * 
    * @return void
    */
    public function deleteGameTransactions()
    {
        $this->buyIns()->delete();

        parent::deleteGameTransactions();
    }
}