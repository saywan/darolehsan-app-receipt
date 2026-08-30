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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('receiver_name')->nullable(); // نام گیرنده
            $table->string('mobile'); // شماره موبایل
            $table->text('message'); // متن دقیق پیامک
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending'); // وضعیت
            $table->text('error_message')->nullable(); // متن خطایی که از سمت سرویس پیامک برگشته
            $table->string('type')->nullable(); // نوع پیامک (مثلا: تحویل_صندوق، تخلیه_صندوق)
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
        Schema::dropIfExists('sms_logs');
    }
};
