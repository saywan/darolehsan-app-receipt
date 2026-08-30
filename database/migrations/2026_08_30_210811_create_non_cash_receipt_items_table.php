<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('non_cash_receipts', function (Blueprint $table) {
            $table->id();

            // اضافه شدن کلید خارجی کاربر (کارمندی که ثبت کرده) جهت رفع خطای user_id
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('receipt_number', 50)->unique()->comment('شماره سریال رسید');
            $table->string('donor_name', 150)->comment('نام و نام خانوادگی خیر');
            $table->string('donor_mobile', 15)->comment('شماره موبایل خیر');
            $table->string('national_code', 10)->nullable()->comment('کد ملی خیر');
            $table->date('receipt_date')->comment('تاریخ ثبت رسید');
            $table->unsignedBigInteger('total_estimated_value')->default(0)->comment('مجموع ارزش ریالی تخمینی (تومان/ریال)');
            $table->text('notes')->nullable()->comment('توضیحات و یادداشت‌ها');
            $table->boolean('sms_sent')->default(false)->comment('وضعیت ارسال پیامک تشکر');

            $table->timestamps();

            // ایندکس‌ها جهت بهینه‌سازی جستجو
            $table->index('donor_mobile');
            $table->index('receipt_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_cash_receipts');
    }
};
