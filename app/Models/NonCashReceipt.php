<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonCashReceipt extends Model
{
    use HasFactory;

    /**
     * نام جدول دیتابیس
     */
    protected $table = 'non_cash_receipts';

    /**
     * فیلدهای قابل مقداردهی انبوه (Mass Assignable)
     */
    protected $fillable = [
        'receipt_number',
        'user_id',
        'donor_name',
        'donor_mobile',
        'donor_phone',
        'donor_address',
        'receipt_date',
        'delivered_by',
        'delivered_by_phone',
        'receiver_name',
        'description',
        'estimated_total_value',
        'status',
    ];

    /**
     * تبدیل نوع فیلدها (Casting)
     */
    protected $casts = [
        'receipt_date' => 'date',
        'estimated_total_value' => 'decimal:2',
    ];

    /**
     * رابطه با کاربر ثبت‌کننده رسید (جهت رفع خطای RelationNotFoundException)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * رابطه با اقلام ثبت‌شده برای این رسید
     */
    public function items()
    {
        return $this->hasMany(NonCashReceiptItem::class, 'non_cash_receipt_id');
    }
}
