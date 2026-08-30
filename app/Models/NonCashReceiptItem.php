<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonCashReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'non_cash_receipt_items';

    protected $fillable = [
        'non_cash_receipt_id',
        'item_title',
        'category',
        'quantity',
        'unit',
        'item_condition',
        'estimated_value',
        'description',
    ];

    /**
     * رابطه بازگشتی با رسید اصلی
     */
    public function receipt()
    {
        return $this->belongsTo(NonCashReceipt::class, 'non_cash_receipt_id');
    }
}
