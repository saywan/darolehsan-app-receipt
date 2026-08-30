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
        Schema::create('non_cash_receipt_items', function (Blueprint $table) {
            $table->id();

            // کلید خارجی متصل به جدول non_cash_receipts با حذف آبشاری
            $table->foreignId('non_cash_receipt_id')
                ->constrained('non_cash_receipts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('item_name', 150)->comment('نام کالا یا خدمت اهدایی');
            $table->string('category', 100)->nullable()->comment('دسته‌بندی (پوشاک، ارزاق، جهیزیه، درمانی و ...)');
            $table->decimal('quantity', 10, 2)->default(1)->comment('تعداد یا مقدار');
            $table->string('unit', 50)->default('عدد')->comment('واحد سنجش (عدد، کیلوگرم، بسته، متر و ...)');
            $table->unsignedBigInteger('estimated_unit_price')->default(0)->comment('ارزش واحد تخمینی');
            $table->unsignedBigInteger('total_price')->default(0)->comment('ارزش کل قلم کالا');
            $table->enum('condition', ['new', 'used_good', 'used_fair'])->default('new')->comment('وضعیت کالا: نو، در حد نو، مستعمل');
            $table->string('description', 255)->nullable()->comment('توضیحات تکمیلی قلم کالا');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_cash_receipt_items');
    }
};
