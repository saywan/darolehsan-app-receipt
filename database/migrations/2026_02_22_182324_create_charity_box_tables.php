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

        Schema::create('charity_boxes', function (Blueprint $table) {
            $table->id();

            // تغییر مهم: ->unique() از اینجا حذف شد
            $table->string('code'); // کد صندوق یا هولوگرام

            $table->enum('type', ['plastic', 'glass']); // پلاستیکی یا شیشه‌ای
            $table->enum('status', ['available', 'assigned', 'destroyed'])->default('available');
            // available: موجود در انبار (برای شیشه ای)
            // assigned: دست مشترک
            // destroyed: پاره شده (برای پلاستیکی)

            // تغییر مهم: اضافه کردن ایندکس یکتای ترکیبی (کد + نوع)
            // حالا سیستم اجازه می‌دهد کد 123 پلاستیکی و 123 شیشه‌ای همزمان وجود داشته باشند
            $table->unique(['code', 'type']);

            $table->timestamps();
        });

        // جدول تخصیص و تراکنش‌ها (چه کسی صندوق را دارد؟) - بدون تغییر
        Schema::create('box_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('box_id')->constrained('charity_boxes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // کارمندی که صندوق را تحویل داده

            // اطلاعات تحویل گیرنده (مشترک)
            $table->string('applicant_name');
            $table->string('applicant_national_code')->nullable();
            $table->string('applicant_mobile');
            $table->text('applicant_address')->nullable();

            $table->timestamp('assigned_at'); // تاریخ تحویل به مشترک
            $table->timestamp('collected_at')->nullable(); // تاریخ تخلیه/عودت

            $table->decimal('amount', 15, 0)->nullable()->default(0); // مبلغ جمع آوری شده

            $table->enum('status', ['active', 'collected'])->default('active');
            // active: هنوز دست مشترک است
            // collected: تخلیه و ثبت شده

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
        Schema::dropIfExists('box_allocations');
        Schema::dropIfExists('charity_boxes');
    }
};
