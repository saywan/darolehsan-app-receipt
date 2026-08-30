<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mobile',
        'password',
        'otp_code',
        'otp_expires_at',
        'mobile_verified_at',
        'is_admin',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code', // کد تایید نباید در خروجی API دیده شود
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'otp_expires_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime', // مهم برای مقایسه زمان
    ];
    /**
     * رابطه با مدل Receipt
     * هر کاربر (اپراتور) می‌تواند چندین قبض صادر کند.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'creator_id');
    }
}
