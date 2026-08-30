<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReceiptBatch;
use Illuminate\Support\Facades\Auth;

class ReceiptBatchController extends Controller
{
    //

    /**
     * نمایش لیست دسته‌های قبض کاربر جاری
     */
    public function index(Request $request)
    {
        // 1. شروع کوئری فقط برای کاربر لاگین شده
        // تغییر مهم: اضافه کردن withCount('receipts') برای شمارش تعداد واقعی فیش‌های صادر شده در دیتابیس
        // این باعث می‌شود درصد پیشرفت در ویو index.blade.php دقیق محاسبه شود.
        $query = ReceiptBatch::where('user_id', Auth::id())
            ->withCount('receipts');

        // 2. اعمال جستجو اگر پارامتر search وجود داشته باشد
        if ($keyword = $request->get('search')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                    ->orWhere('start_number', 'like', "%{$keyword}%")
                    ->orWhere('end_number', 'like', "%{$keyword}%")
                    ->orWhere('status', 'like', "%{$keyword}%");
            });
        }

        // 3. مرتب‌سازی و صفحه‌بندی
        $batches = $query->latest()->paginate(10);

        return view('employee.batches.index', compact('batches'));
    }

    /**
     * نمایش ریز فیش‌های یک دسته خاص
     * (فقط اگر دسته متعلق به همین کاربر باشد)
     */
    public function receipts(Request $request, $id)
    {
        // پیدا کردن دسته با شرط مالکیت کاربر (جلوگیری از دیدن فیش‌های دیگران)
        $batch = ReceiptBatch::where('user_id', Auth::id())->findOrFail($id);

        // کوئری روی فیش‌های این دسته
        $query = $batch->receipts();

        // اعمال جستجو در فیش‌ها
        if ($keyword = $request->get('search')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('donor_name', 'like', "%{$keyword}%")
                    ->orWhere('serial_number', 'like', "%{$keyword}%")
                    ->orWhere('amount_rials', 'like', "%{$keyword}%") // جستجو در ریال
                    ->orWhere('donor_mobile', 'like', "%{$keyword}%")
                    ->orWhere('help_type', 'like', "%{$keyword}%");
            });
        }

        $receipts = $query->latest()->paginate(20);

        // بازگشت به ویوی ریز فیش‌ها
        return view('employee.batches.receipts', compact('batch', 'receipts'));
    }

    public function create()
    {
        $userId = Auth::id();

        // 1. بررسی محدودیت: آیا کاربر دسته فعال دارد؟
        $activeBatch = ReceiptBatch::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if ($activeBatch) {
            return redirect()->route('employee.batches.index')
                ->with('error', 'شما در حال حاضر یک دسته قبض فعال (تمام نشده) دارید. ابتدا آن را به اتمام برسانید تا بتوانید دسته جدید دریافت کنید.');
        }

        // 2. پیشنهاد شماره شروع (بزرگترین پایان + 1)
        // اگر هیچ دسته‌ای در سیستم نباشد، از 1 شروع می‌کند
        $lastBatchInSystem = ReceiptBatch::orderBy('end_number', 'desc')->first();
        $suggestedStart = $lastBatchInSystem ? ($lastBatchInSystem->end_number + 1) : 1;

        return view('employee.batches.create', compact('suggestedStart'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        // 1. بررسی مجدد محدودیت (برای امنیت بیشتر)
        $hasActiveBatch = ReceiptBatch::where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveBatch) {
            return redirect()->route('employee.batches.index')
                ->with('error', 'شما یک دسته فعال دارید و نمی‌توانید دسته جدید ایجاد کنید.');
        }

        // 2. اعتبارسنجی ورودی‌ها
        $request->validate([
            'start_number' => 'required|integer|min:1',
            'end_number'   => 'required|integer|gt:start_number', // پایان باید بزرگتر از شروع باشد
        ], [
            'end_number.gt' => 'شماره پایان باید بزرگتر از شماره شروع باشد.',
            'start_number.required' => 'شماره شروع الزامی است.',
            'end_number.required' => 'شماره پایان الزامی است.',
        ]);

        // 3. بررسی تداخل بازه‌ها (Overlap Check)
        // چک میکنیم که بازه جدید با هیچ بازه دیگری در کل سیستم تداخل نداشته باشد
        $overlap = ReceiptBatch::where(function ($query) use ($request) {
            $query->whereBetween('start_number', [$request->start_number, $request->end_number])
                ->orWhereBetween('end_number', [$request->start_number, $request->end_number])
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_number', '<', $request->start_number)
                        ->where('end_number', '>', $request->end_number);
                });
        })->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['start_number' => 'این بازه شماره سریال با دسته‌های موجود تداخل دارد. لطفاً بازه دیگری انتخاب کنید.']);
        }

        // 4. ایجاد دسته جدید
        ReceiptBatch::create([
            'user_id'        => $userId,
            'start_number'   => $request->start_number,
            'end_number'     => $request->end_number,
            'current_number' => $request->start_number, // شماره فعلی همان شماره شروع است
            'status'         => 'active',
        ]);

        return redirect()->route('employee.batches.index')
            ->with('success', 'دسته قبض جدید با موفقیت تعریف شد.');
    }

    /**
     * نمایش فرم ویرایش
     * مسیر: /panel/batches/{id}/edit
     */
    public function edit($id)
    {
        $batch = ReceiptBatch::findOrFail($id);
        return view('employee.batches.edit', compact('batch'));
    }

    /**
     * ذخیره تغییرات ویرایش
     * مسیر: /panel/batches/{id} (متد PUT)
     */
    public function update(Request $request, $id)
    {
        $batch = ReceiptBatch::findOrFail($id);

        // 1. اعتبارسنجی
        $request->validate([
            'start_number'   => 'required|integer|min:1',
            'end_number'     => 'required|integer|gt:start_number', // پایان باید بزرگتر از شروع باشد
            'current_number' => 'required|integer|gte:start_number|lte:end_number', // شماره فعلی باید بین بازه باشد
            'status'         => 'required|in:active,finished,inactive',
        ], [
            'end_number.gt' => 'شماره پایان باید بزرگتر از شماره شروع باشد.',
            'current_number.gte' => 'شماره فعلی نمی‌تواند کمتر از شماره شروع باشد.',
            'current_number.lte' => 'شماره فعلی نمی‌تواند بیشتر از شماره پایان باشد.',
        ]);

        // 2. اعمال تغییرات
        $batch->start_number   = $request->start_number;
        $batch->end_number     = $request->end_number;
        $batch->current_number = $request->current_number;
        $batch->status         = $request->status;

        // اگر دستی شماره را به آخر رساندند، وضعیت را تمام شده کن
        if ($batch->current_number > $batch->end_number) {
            $batch->status = 'finished';
        }

        $batch->save();

        // 3. بازگشت
        return redirect()->route('employee.batches.index')
            ->with('success', 'دسته قبض با موفقیت ویرایش شد.');
    }

}
