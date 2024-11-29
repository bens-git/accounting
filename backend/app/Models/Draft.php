<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use HasFactory;
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = ['name', 'type', 'party_id', 'amount', 'payment_method', 'details', 'tag', 'user_id', 'recipient_id', 'recurrence_type', 'recurrence_start_date', 'recurrence_end_date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }
}
