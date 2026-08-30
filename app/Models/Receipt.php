<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Receipt extends Model
{
    use HasFactory;
    /**
     * نام جدول در دیتابیس (اختیاری، لاراول خودکار تشخیص می‌دهد اما برای اطمینان)
     */
    protected $table = 'receipts';

    /**
     * فیلدهایی که اجازه پر شدن دارند
     */
    protected $fillable = [

        'user_id',         // کاربر صادر کننده
        'serial_number',   // شماره سریال قرمز
        'doc_code',        // کد مدرک
        'receipt_date',    // تاریخ
        'donor_name',      // نام نیکوکار
        'donor_mobile',    // موبایل
        'amount_rials',    // مبلغ (ریال) ذخیره می‌شود
        'amount_words',    // مبلغ به حروف
        'box_code_old',    // (در فرم استفاده نمی‌شود اما در دیتابیس هست)
        'box_code_new',    // (در فرم استفاده نمی‌شود اما در دیتابیس هست)
        'payment_type',    // نوع پرداخت (نقد، کارتخوان و...)
        'description',     // توضیحات
        'help_type',       // نوع کمک (زکات، صدقه، ...)

    ];
    /**
     * کستینگ (تبدیل نوع داده‌ها هنگام خواندن/نوشتن)
     */
    protected $casts = [
        'receipt_date' => 'datetime',
        'amount_rials' => 'integer',
        'serial_number' => 'integer',
    ];
    /**
     * رابطه با مدل User
     * هر قبض توسط یک کاربر (اپراتور) صادر شده است.
     */
     /**
     * ارتباط با کاربر صادر کننده
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ارتباط با مدل دسته قبض
     */
    public function batch()
    {
        return $this->belongsTo(ReceiptBatch::class, 'receipt_batch_id');
    }

}
