<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use App\Services\NegarSmsService;
use App\Models\SmsLog;
use Exception;

class NonCashReceiptController extends Controller
{
    protected $smsService;

    // تزریق سرویس پیامک
    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش لیست رسیدها
     */
    public function index()
    {
        $receipts = DB::table('non_cash_receipts')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('employee.non_cash_receipts.index', compact('receipts'));
    }

    /**
     * نمایش فرم ثبت رسید جدید
     */
    public function create()
    {
        $lastReceipt = DB::table('non_cash_receipts')->orderBy('id', 'desc')->first();
        $suggestedReceiptNumber = $lastReceipt ? ((int)$lastReceipt->receipt_number + 1) : 1;

        $currentDate = class_exists(Jalalian::class)
            ? Jalalian::now()->format('Y/m/d')
            : date('Y/m/d');

        return view('employee.non_cash_receipts.create', compact('suggestedReceiptNumber', 'currentDate'));
    }

    /**
     * ذخیره‌سازی رسید و اقلام آن
     */
    public function store(Request $request)
    {
        // ۱. اعتبارسنجی فیلدهای سربرگ و لیست اقلام
        $validated = $request->validate([
            'receipt_number' => 'required|string|max:50|unique:non_cash_receipts,receipt_number',
            'receipt_date'   => 'required|string',
            'donor_name'     => 'required|string|max:150',
            'donor_mobile'   => 'required|string|max:15',
            'national_code'  => 'nullable|string|max:10',
            'notes'          => 'nullable|string',

            'items'                        => 'required|array|min:1',
            'items.*.item_name'            => 'required|string|max:150',
            'items.*.category'             => 'nullable|string|max:100',
            'items.*.quantity'             => 'required|numeric|min:0.01',
            'items.*.unit'                 => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'nullable|string',
            'items.*.condition'            => 'required|in:new,used_good,used_fair',
            'items.*.description'          => 'nullable|string|max:255',
        ], [
            'items.*.item_name.required' => 'وارد کردن نام کالا الزامی است.',
            'items.*.quantity.required'  => 'مقدار کالا الزامی است.',
            'items.*.unit.required'      => 'واحد کالا الزامی است.',
        ]);

        $receiptDate = Carbon::now()->toDateString();
        $jalaliDate = class_exists(Jalalian::class) ? Jalalian::now()->format('Y/m/d') : date('Y/m/d');
        try {
            if (class_exists(Jalalian::class) && !empty($request->receipt_date)) {
                $cleanDate = str_replace('-', '/', trim($request->receipt_date));
                $receiptDate = Jalalian::fromFormat('Y/m/d', $cleanDate)->toCarbon()->toDateString();
                $jalaliDate = $cleanDate;
            }
        } catch (Exception $e) {
            $receiptDate = Carbon::now()->toDateString();
        }

        DB::beginTransaction();
        try {
            $userId = Auth::id() ?? 1;
            $now = Carbon::now();

            // الف) درج سربرگ
            $receiptId = DB::table('non_cash_receipts')->insertGetId([
                'user_id'               => $userId,
                'receipt_number'        => $request->receipt_number,
                'donor_name'            => $request->donor_name,
                'donor_mobile'          => $request->donor_mobile,
                'national_code'         => $request->national_code,
                'receipt_date'          => $receiptDate,
                'total_estimated_value' => 0,
                'notes'                 => $request->notes,
                'sms_sent'              => 0, // فعلاً ۰، در صورت ارسال موفق ۱ می‌شود
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);

            // ب) پردازش اقلام
            $grandTotal = 0;
            $itemsData = [];
            $itemNamesList = []; // برای استفاده در متن پیامک

            foreach ($request->items as $item) {
                $unitPrice = isset($item['estimated_unit_price']) ? (int)str_replace(',', '', $item['estimated_unit_price']) : 0;
                $quantity = (float)$item['quantity'];
                $totalPrice = (int)($unitPrice * $quantity);

                $grandTotal += $totalPrice;
                $itemNamesList[] = $item['item_name']; // جمع‌آوری نام کالاها

                $itemsData[] = [
                    'non_cash_receipt_id'  => $receiptId,
                    'item_name'            => $item['item_name'],
                    'category'             => $item['category'] ?? null,
                    'quantity'             => $quantity,
                    'unit'                 => $item['unit'] ?? 'عدد',
                    'estimated_unit_price' => $unitPrice,
                    'total_price'          => $totalPrice,
                    'condition'            => $item['condition'] ?? 'new',
                    'description'          => $item['description'] ?? null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            // درج اقلام
            DB::table('non_cash_receipt_items')->insert($itemsData);

            // ج) آپدیت جمع کل
            DB::table('non_cash_receipts')
                ->where('id', $receiptId)
                ->update(['total_estimated_value' => $grandTotal]);

            DB::commit();

            // د) ارسال پیامک رسید غیرنقدی
            $smsStatus = 'ارسال نشد';
            if (!empty($request->donor_mobile)) {
                $smsResult = $this->sendNonCashReceiptSms(
                    $request->donor_name,
                    $request->donor_mobile,
                    $jalaliDate,
                    $itemNamesList,
                    $receiptId,
                    $userId
                );

                if ($smsResult) {
                    // در صورت موفقیت، آپدیت فیلد sms_sent در دیتابیس
                    DB::table('non_cash_receipts')->where('id', $receiptId)->update(['sms_sent' => 1]);
                    $smsStatus = 'و پیامک ارسال شد';
                } else {
                    $smsStatus = 'اما ارسال پیامک با خطا مواجه شد';
                }
            }

            return redirect()->route('employee.non-cash-receipts.index')
                ->with('success', "رسید اهدای کالایی با شماره {$request->receipt_number} با موفقیت ثبت شد {$smsStatus}.");
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'متأسفانه در ثبت اطلاعات خطایی رخ داد: ' . $ex->getMessage());
        }
    }

    /**
     * متد خصوصی برای ارسال پیامک رسید غیرنقدی
     */
    private function sendNonCashReceiptSms($donorName, $mobile, $date, $itemsList, $receiptId, $userId)
    {
        try {
            // ساخت رشته‌ای از نام کالاها (حداکثر ۲ مورد برای جلوگیری از طولانی شدن پیامک)
            $itemsText = implode(' و ', array_slice($itemsList, 0, 2));
            if (count($itemsList) > 2) {
                $itemsText .= ' و ...';
            }

            // متن پیامک برای کالاهای غیرنقدی
            $message = "نیکوکار گرامی جناب/سرکار {$donorName}\n"
                . "با سلام، بابت اهدای کالای «{$itemsText}» در تاریخ {$date} به موسسه خیریه دارالاحسان از شما سپاسگزاریم.\n"
                . "کد رسید: {$receiptId}";

            // فراخوانی سرویس پیامک
            $response = $this->smsService->sendOneToMany($message, [$mobile]);

            // ثبت لاگ پیامک موفق
            SmsLog::create([
                'user_id'       => $userId,
                'donor_name'    => $donorName,
                'donor_mobile'  => $mobile,
                'message_text'  => $message,
                'type'          => 'صدور فیش غیرنقدی',
                'status'        => 'sent',
                'error_message' => null,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Non-Cash SMS Failed for Receipt ID {$receiptId}: " . $e->getMessage());

            // ثبت لاگ پیامک ناموفق
            SmsLog::create([
                'user_id'       => $userId,
                'donor_name'    => $donorName,
                'donor_mobile'  => $mobile,
                'message_text'  => $message ?? 'خطا در ساخت متن',
                'type'          => 'صدور فیش غیرنقدی',
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
