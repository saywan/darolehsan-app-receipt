<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_number',
        'end_number',
        'current_number',
        'status',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    // اسکوپ برای چک کردن تداخل بازه‌ها (بسیار مهم)
    public function scopeOverlaps($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_number', '<=', $end)
                ->where('end_number', '>=', $start);
        });
    }
}
