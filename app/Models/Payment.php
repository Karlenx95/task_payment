<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        "merchant_id",
        "payment_id",
        "status",
        "amount",
        "amount_paid",
        "limit",
        "provider",
        "project",
        "invoice",
        "sign",
    ];

    public function payment()
    {
        return $this->belongsTo(User::class);
    }
}
