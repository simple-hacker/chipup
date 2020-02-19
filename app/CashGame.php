<?php

namespace App;

use Illuminate\Support\Carbon;
use App\Exceptions\InvalidDate;
use Illuminate\Database\Eloquent\Model;

class CashGame extends Model
{
    protected $guarded = [];

    /**
    * A session belongs to a user 
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
    * End the Cash Game by updating the end_time to the current time or given time.
    * 
    * @param 
    * @return bool
    */
    public function end(Carbon $end_time = null)
    {
        $end_time  = $end_time ?? now();

        // The end_time cannot be before the start_time
        if ($end_time < $this->start_time) {
            throw new InvalidDate;
        }

        return $this->update([
            'end_time' => $end_time
        ]);
    }
}
