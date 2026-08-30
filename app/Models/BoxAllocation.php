<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxAllocation extends Model
{
    use HasFactory;
    // فیلدهای مدل قبلی شما + فیلدهای کنترلر جدید با هم ادغام شدند
    // این بخش بسیار مهم است و اجازه ذخیره این فیلدها را در دیتابیس می‌دهد
    protected $fillable = [
        'box_id',
        'user_id',
        'applicant_name',
        'applicant_national_code',
        'applicant_mobile',
        'applicant_address',
        'assigned_at',
        'collected_at',
        'amount',
        'status'
    ];

    // ارتباط با جدول صندوق
    public function charityBox()
    {
        return $this->belongsTo(CharityBox::class, 'box_id');
    }

    // برای سازگاری با کنترلرهای دیگر اگر نیاز بود
    public function box()
    {
        return $this->belongsTo(CharityBox::class, 'box_id');
    }
}
