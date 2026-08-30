<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NonCashReceipt;
use App\Models\NonCashReceiptItem;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Auth;
use App\Services\NegarSmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class NonCashReceiptController extends Controller
{
    protected $smsService;

    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش لیست کمک‌های غیرنقدی ثبت‌شده توسط کارمند
     */
    public function index(Request $request)
    {
        $query = NonCashReceipt::where('user_id', Auth::id())
            ->with(['user', 'items'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                    ->orWhere('donor_mobile', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $receipts = $query->paginate(15);

        return view('employee.non_cash_receipts.index', compact('receipts'));
    }

    /**
     * فرم ثبت کمک غیرنقدی جدید
     */
    public function create()
    {
        // تولید شماره رسید پیشنهادی
        $nextReceiptNumber = 'NC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return view('employee.non_cash_receipts.create', compact('nextReceiptNumber'));
    }

    /**
     * ذخیره‌سازی رسید و اقلام غیرنقدی
     */
    public function store(Request $request)
    {
        $request->validate([
            'donor_name'          => 'required|string|max:255',
            'donor_mobile'        => 'nullable|string|max:20',
            'donor_phone'         => 'nullable|string|max:20',
            'donor_address'       => 'nullable|string|max:500',
            'receipt_date'        => 'nullable|string|max:20',
            'delivered_by'        => 'nullable|string|max:255',
            'delivered_by_phone'  => 'nullable|string|max:20',
            'receiver_name'       => 'nullable|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.item_title'  => 'required|string|max:255',
            'items.*.category'    => 'nullable|string|max:100',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'required|string|max:50',
            'items.*.estimated_value' => 'nullable|string',
        ], [
            'donor_name.required'         => 'نام و نام خانوادگی اهداکننده الزامی است.',
            'items.required'              => 'حداقل باید یک قلم کالا ثبت شود.',
            'items.*.item_title.required' => 'عنوان کالا الزامی است.',
            'items.*.quantity.required'   => 'تعداد / مقدار کالا الزامی است.',
            'items.*.unit.required'       => 'واحد شمارش کالا الزامی است.',
        ]);

        $donorMobile = $this->convertPersianNumbers($request->input('donor_mobile'));
        $receiptDate = now();

        if ($request->filled('receipt_date')) {
            try {
                $cleanDate = $this->convertPersianNumbers($request->input('receipt_date'));
                $parts = explode('/', $cleanDate);
                if (count($parts) === 3) {
                    $receiptDate = (new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2], 0, 0, 0))->toCarbon();
                }
            } catch (\Exception $e) {
                Log::warning('خطا در تبدیل تاریخ شمسی فیش غیرنقدی: ' . $e->getMessage());
                $receiptDate = now();
            }
        }

        DB::beginTransaction();
        try {
            $receiptNumber = $request->input('receipt_number') ?? ('NC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)));

            $receipt = NonCashReceipt::create([
                'user_id'               => Auth::id(),
                'receipt_number'        => $receiptNumber,
                'donor_name'            => $request->input('donor_name'),
                'donor_mobile'          => $donorMobile,
                'donor_phone'           => $this->convertPersianNumbers($request->input('donor_phone')),
                'donor_address'         => $request->input('donor_address'),
                'receipt_date'          => $receiptDate,
                'delivered_by'          => $request->input('delivered_by'),
                'delivered_by_phone'    => $this->convertPersianNumbers($request->input('delivered_by_phone')),
                'receiver_name'         => $request->input('receiver_name'),
                'description'           => $request->input('description'),
                'estimated_total_value' => 0,
                'status'                => 'received',
            ]);

            $totalValue = 0;
            foreach ($request->input('items') as $itemData) {
                $rawVal = isset($itemData['estimated_value']) ? str_replace(',', '', $this->convertPersianNumbers($itemData['estimated_value'])) : 0;
                $estimatedValue = (float) $rawVal;
                $totalValue += $estimatedValue;

                NonCashReceiptItem::create([
                    'non_cash_receipt_id' => $receipt->id,
                    'item_title'          => $itemData['item_title'],
                    'category'            => $itemData['category'] ?? null,
                    'quantity'            => (float) $this->convertPersianNumbers($itemData['quantity']),
                    'unit'                => $itemData['unit'],
                    'item_condition'      => $itemData['item_condition'] ?? 'نو',
                    'estimated_value'     => $estimatedValue > 0 ? $estimatedValue : null,
                    'description'         => $itemData['description'] ?? null,
                ]);
            }

            $receipt->update(['estimated_total_value' => $totalValue]);

            DB::commit();

            // ارسال پیامک بعد از اطمینان از صحت ثبت در دیتابیس
            if (!empty($donorMobile)) {
                $this->sendReceiptSms($receipt);
            }

            return redirect()->route('employee.non-cash-receipts.index')
                ->with('success', 'رسید کمک غیرنقدی با موفقیت ثبت شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در ثبت رسید غیرنقدی: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'خطایی در ثبت فیش رخ داد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * نمایش جزئیات رسید
     */
    public function show($id)
    {
        $receipt = NonCashReceipt::where('user_id', Auth::id())
            ->with(['items', 'user'])
            ->findOrFail($id);

        return view('employee.non_cash_receipts.show', compact('receipt'));
    }

    /**
     * فرم ویرایش رسید
     */
    public function edit($id)
    {
        $receipt = NonCashReceipt::where('user_id', Auth::id())
            ->with('items')
            ->findOrFail($id);

        return view('employee.non_cash_receipts.edit', compact('receipt'));
    }

    /**
     * بروزرسانی رسید غیرنقدی
     */
    public function update(Request $request, $id)
    {
        $receipt = NonCashReceipt::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'donor_name'          => 'required|string|max:255',
            'donor_mobile'        => 'nullable|string|max:20',
            'donor_phone'         => 'nullable|string|max:20',
            'donor_address'       => 'nullable|string|max:500',
            'delivered_by'        => 'nullable|string|max:255',
            'delivered_by_phone'  => 'nullable|string|max:20',
            'receiver_name'       => 'nullable|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.item_title'  => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'required|string|max:50',
        ]);

        $donorMobile = $this->convertPersianNumbers($request->input('donor_mobile'));

        DB::beginTransaction();
        try {
            $receipt->update([
                'donor_name'         => $request->input('donor_name'),
                'donor_mobile'       => $donorMobile,
                'donor_phone'        => $this->convertPersianNumbers($request->input('donor_phone')),
                'donor_address'      => $request->input('donor_address'),
                'delivered_by'       => $request->input('delivered_by'),
                'delivered_by_phone' => $this->convertPersianNumbers($request->input('delivered_by_phone')),
                'receiver_name'      => $request->input('receiver_name'),
                'description'        => $request->input('description'),
            ]);

            // بازسازی اقلام
            $receipt->items()->delete();
            $totalValue = 0;

            foreach ($request->input('items') as $itemData) {
                $rawVal = isset($itemData['estimated_value']) ? str_replace(',', '', $this->convertPersianNumbers($itemData['estimated_value'])) : 0;
                $estimatedValue = (float) $rawVal;
                $totalValue += $estimatedValue;

                NonCashReceiptItem::create([
                    'non_cash_receipt_id' => $receipt->id,
                    'item_title'          => $itemData['item_title'],
                    'category'            => $itemData['category'] ?? null,
                    'quantity'            => (float) $this->convertPersianNumbers($itemData['quantity']),
                    'unit'                => $itemData['unit'],
                    'item_condition'      => $itemData['item_condition'] ?? 'نو',
                    'estimated_value'     => $estimatedValue > 0 ? $estimatedValue : null,
                    'description'         => $itemData['description'] ?? null,
                ]);
            }

            $receipt->update(['estimated_total_value' => $totalValue]);

            DB::commit();

            return redirect()->route('employee.non-cash-receipts.index')
                ->with('success', 'فیش کمک غیرنقدی با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در ویرایش رسید غیرنقدی: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'خطایی در ویرایش رخ داد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف رسید غیرنقدی
     */
    public function destroy($id)
    {
        $receipt = NonCashReceipt::where('user_id', Auth::id())->findOrFail($id);

        try {
            DB::beginTransaction();
            $receipt->items()->delete();
            $receipt->delete();
            DB::commit();

            return redirect()->route('employee.non-cash-receipts.index')
                ->with('success', 'فیش غیرنقدی با موفقیت حذف شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در حذف رسید غیرنقدی: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطا در حذف رکورد.');
        }
    }

    /**
     * ارسال پیامک تشکر و ثبت در لاگ
     */
    private function sendReceiptSms($receipt)
    {
        try {
            $date = Jalalian::fromCarbon($receipt->receipt_date ?? now())->format('Y/m/d');
            $itemCount = $receipt->items()->count();

            $message = "خیر گرامی جناب آقای/خانم {$receipt->donor_name}\n"
                . "کمک‌های غیرنقدی شما (شامل {$itemCount} قلم کالا) در تاریخ {$date} با موفقیت دریافت و ثبت گردید.\n"
                . "از همراهی و احسان شما سپاسگزاریم.";

            $mobile = $receipt->donor_mobile;

            if (empty($mobile)) {
                return;
            }

            $response = $this->smsService->sendOneToMany($message, [$mobile]);

            SmsLog::create([
                'user_id'       => Auth::id(),
                'type'          => 'کمک غیرنقدی',
                'mobile_number' => $mobile,
                'message'       => $message,
                'status'        => 'sent',
                'response_data' => is_array($response) ? json_encode($response, JSON_UNESCAPED_UNICODE) : (string)$response,
            ]);
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیامک رسید غیرنقدی: ' . $e->getMessage());

            try {
                SmsLog::create([
                    'user_id'       => Auth::id(),
                    'type'          => 'کمک غیرنقدی',
                    'mobile_number' => $receipt->donor_mobile ?? 'نامشخص',
                    'message'       => $message ?? 'عدم تشکیل متن پیام',
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            } catch (\Exception $logEx) {
                Log::critical('خطا در ثبت لاگ پیامک ناموفق: ' . $logEx->getMessage());
            }
        }
    }

    /**
     * تبدیل اعداد فارسی و عربی به انگلیسی
     */
    private function convertPersianNumbers($string)
    {
        if (empty($string)) {
            return $string;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $string);
    }
}
