<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "credit_card_number",
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
