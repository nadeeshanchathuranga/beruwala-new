<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shifts';

    protected $fillable = [
        'user_id',
        'start_time',
        'start_amount',
        'end_time',
        'end_amount',
        'total_sales',
        'notes',
        'status',
    ];

 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(TillTransaction::class, 'shift_id');
    }
}
