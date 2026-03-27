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
        'closing_notes',
        'expected_cash',
        'variance_amount',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_amount' => 'decimal:2',
        'end_amount' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'variance_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'shift_id');
    }

    public function transactions()
    {
        return $this->hasMany(TillTransaction::class, 'shift_id');
    }
}
