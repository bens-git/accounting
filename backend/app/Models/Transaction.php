<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'party', 'amount', 'date', 'payment_method', 'details', 'tag', 'user_id', 'recipient_id', 'recurrence_type', 'recurrence_start_date', 'recurrence_end_date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
