<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // 1. ساخت جدول کاربران با فیلدهای موبایل و OTP
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile')->unique(); // موبایل به عنوان شناسه یکتا
            $table->string('password');

            // فیلدهای احراز هویت پیامکی
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();

            $table->boolean('is_admin')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. ساخت جدول لاگ‌های سیستم
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // نوع عملیات (Login, Register, etc)
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device')->nullable(); // User Agent
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('users');
    }
};
