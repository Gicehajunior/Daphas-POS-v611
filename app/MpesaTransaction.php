<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mpesa_transactions';

    /**
     * Get the Cash registers transactions.
     */
    public function mpesa_transactions()
    {
        return $this->hasMany(\App\Transaction::class);
    }
}
