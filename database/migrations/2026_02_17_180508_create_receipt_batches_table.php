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
        // 1. ساخت جدول دسته‌های قبض
        Schema::create('receipt_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // کارمندی که دسته را ساخته
            $table->bigInteger('start_number')->unsigned();
            $table->bigInteger('end_number')->unsigned();
            $table->bigInteger('current_number')->unsigned(); // شماره بعدی که باید صادر شود
            $table->enum('status', ['active', 'finished', 'blocked'])->default('active');
            $table->string('description')->nullable();
            $table->timestamps();

            // جلوگیری از تداخل در سطح دیتابیس (اختیاری اما توصیه شده)
            // $table->unique(['start_number', 'end_number']);
        });

        // 2. اصلاح جدول رسیدها (Receipts) برای اتصال به دسته
        Schema::table('receipts', function (Blueprint $table) {
            // اضافه کردن ستون با قابلیت نال بودن (برای فیش‌های قدیمی)
            $table->foreignId('receipt_batch_id')->nullable()->after('user_id')
                ->constrained('receipt_batches')->nullOnDelete();

            // تغییر نوع ستون سریال نامبر به عدد (اگر قبلا string بود و حالا می‌خواهید عدد خالص باشد)
            // اگر می‌خواهید فرمت خاصی مثل "1402-1001" داشته باشید، string بماند.
            // اما چون سیستم دسته‌ای معمولا عدد پیاپی است، اینجا فرض بر عدد بودن است:
            // $table->bigInteger('serial_number')->change();
        });
    }

    public function down()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['receipt_batch_id']);
            $table->dropColumn('receipt_batch_id');
        });
        Schema::dropIfExists('receipt_batches');
    }
};
