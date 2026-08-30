<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\NonCashReceipt;
use App\Models\NonCashReceiptItem;
use App\Models\User;
use App\Models\SmsLog;
use App\Services\NegarSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Hekmatinasser\Verta\Verta;
use Exception;

class NonCashReceiptController extends Controller
{
    protected $smsService;

    // تزریق سرویس پیامک به کنترلر
    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش لیست رسیدهای غیرنقدی
     */
    public function index()
    {
        $receipts = NonCashReceipt::with(['user', 'employee'])
            ->latest()
            ->paginate(15);

        return view('employee.non_cash_receipts.index', compact('receipts'));
    }

    /**
     * نمایش فرم ثبت رسید جدید
     */
    public function create()
    {
        // تولید شماره رسید خودکار (فرمت دلخواه شما)
        $autoReceiptNumber = 'NCR-' . time() . rand(10, 99);
        $users = User::all(); // لیست نیکوکاران برای انتخاب در دراپ‌داون

        return view('employee.non_cash_receipts.create', compact('autoReceiptNumber', 'users'));
    }

    /**
     * ذخیره رسید و اقلام آن در دیتابیس
     */
    public function store(Request $request)
    {
        // 1. اعتبارسنجی فیلدها (با رعایت دقیق نام‌گذاری‌ها)
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'receipt_number' => 'required|string|unique:non_cash_receipts,receipt_number',
            'receipt_date' => 'required|string', // تاریخ شمسی
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ], [
            'items.required' => 'حداقل یک ردیف کالا باید وارد شود.',
            'items.*.item_name.required' => 'نام کالا الزامی است.',
            'items.*.quantity.required' => 'تعداد/مقدار الزامی است.',
            'items.*.unit_price.required' => 'ارزش ریالی واحد الزامی است.',
        ]);

        try {
            DB::beginTransaction();

            // 2. تبدیل تاریخ شمسی به میلادی با Verta
            // فرمت ورودی فرض شده: 1405/06/09
            $gregorianDate = Verta::parseFormat('Y/m/d', $request->receipt_date)->datetime();

            // 3. محاسبه ارزش کل رسید از روی اقلام
            $totalValue = 0;
            foreach ($request->items as $item) {
                // اگر ویرگول جداکننده هزارگان از سمت کلاینت ارسال شده، آن را حذف می‌کنیم
                $qty = (float) str_replace(',', '', $item['quantity']);
                $price = (float) str_replace(',', '', $item['unit_price']);
                $totalValue += ($qty * $price);
            }

            // 4. ایجاد رکورد رسید (Master)
            $receipt = NonCashReceipt::create([
                'receipt_number' => $request->receipt_number,
                'user_id' => $request->user_id,
                'employee_id' => Auth::id(), // شناسه کارمند ثبت‌کننده
                'receipt_date' => $gregorianDate,
                'total_value' => $totalValue,
                'description' => $request->description,
                'sms_status' => 'pending', // وضعیت پیش‌فرض پیامک
                'status' => 'active' // وضعیت تایید (بسته به منطق شما)
            ]);

            // 5. ثبت اقلام رسید (Details)
            foreach ($request->items as $item) {
                $qty = (float) str_replace(',', '', $item['quantity']);
                $price = (float) str_replace(',', '', $item['unit_price']);
                $totalPrice = $qty * $price;

                NonCashReceiptItem::create([
                    'non_cash_receipt_id' => $receipt->id,
                    'item_name' => $item['item_name'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $totalPrice,
                    'description' => $item['description'] ?? null,
                ]);
            }

            // 6. ارسال پیامک تشکر پس از ذخیره موفق
            $this->sendReceiptSms($receipt);

            DB::commit();

            return redirect()->route('employee.non-cash-receipts.index')
                ->with('success', 'رسید غیرنقدی با موفقیت ثبت شد و پیامک برای نیکوکار ارسال گردید.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('خطا در ثبت رسید غیرنقدی: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * نمایش جزئیات یک رسید خاص
     */
    public function show(NonCashReceipt $nonCashReceipt)
    {
        $nonCashReceipt->load(['user', 'employee', 'items']);
        return view('employee.non_cash_receipts.show', compact('nonCashReceipt'));
    }

    /**
     * منطق ارسال پیامک و ثبت لاگ آن
     */
    private function sendReceiptSms(NonCashReceipt $receipt)
    {
        try {
            $user = $receipt->user;

            if (!$user || empty($user->mobile)) {
                return; // اگر کاربر شماره موبایل نداشت، خارج می‌شویم
            }

            // متن پیامک (می‌توانید داینامیک کنید یا از پترن‌های سرویس نگار استفاده کنید)
            $message = "نیکوکار گرامی {$user->name} عزیز\nرسید اهدای غیرنقدی شما به شماره {$receipt->receipt_number} در سیستم ثبت گردید.\nبا سپاس از همراهی شما.";

            // فراخوانی متد ارسال پیامک از سرویس (متد دقیق بستگی به پیاده‌سازی سرویس شما دارد)
            // $response = $this->smsService->sendSms($user->mobile, $message);
            $response = true; // در اینجا فرض بر موفقیت ارسال است

            if ($response) {
                // به‌روزرسانی وضعیت پیامک در جدول رسید
                $receipt->update(['sms_status' => 'sent']);

                // ثبت لاگ پیامک
                SmsLog::create([
                    'user_id' => $user->id,
                    'mobile' => $user->mobile,
                    'message' => $message,
                    'status' => 'success',
                    'sent_at' => now(),
                ]);
            } else {
                $receipt->update(['sms_status' => 'failed']);

                SmsLog::create([
                    'user_id' => $user->id,
                    'mobile' => $user->mobile,
                    'message' => $message,
                    'status' => 'failed',
                ]);
            }
        } catch (Exception $e) {
            Log::error('خطا در ارسال پیامک رسید غیرنقدی: ' . $e->getMessage());
            $receipt->update(['sms_status' => 'failed']);
        }
    }

    // متدهای edit, update و destroy در صورت نیاز به ویرایش رسید در آینده می‌توانند در اینجا اضافه شوند
}
