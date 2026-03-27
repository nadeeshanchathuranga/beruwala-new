<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TillTransaction extends Model
{
    protected $table = 'till_transactions';

    protected $fillable = [
        'shift_id',
        'user_id',
        'transaction_type',
        'note',
        'amount',
    ];

  

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
