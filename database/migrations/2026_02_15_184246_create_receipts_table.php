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
        Schema::create('receipts', function (Blueprint $table) {
           
              $table->id();
            
            // ارتباط با جدول کاربران
            $table->unsignedBigInteger('user_id');
            // اگر جدول users دارید خط زیر را از کامنت خارج کنید تا کلید خارجی ساخته شود
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // اصلاح مهم: شماره سریال به صورت رشته (String) تا خط تیره و صفر اول را ذخیره کند
            $table->string('serial_number', 191)->unique(); 
            
            $table->string('doc_code', 191)->nullable();
            $table->date('receipt_date');
            
            $table->string('donor_name', 191);
            $table->string('donor_mobile', 191)->nullable();
            
            $table->bigInteger('amount_rials');
            $table->string('amount_words', 191);
            
            $table->string('box_code_old', 191)->nullable();
            $table->string('box_code_new', 191)->nullable();
            
            // طبق فایل SQL شما این فیلد enum بود
            $table->enum('payment_type', ['monthly', 'occasional'])->default('occasional');
            
            // فیلدی که در SQL نبود اما در کد استفاده شده بود
            $table->string('help_type', 191)->nullable();
            
            $table->string('description', 191)->nullable();
            
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
        Schema::dropIfExists('receipts');
    }
};
