<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use App\Services\NegarSmsService; // اضافه کردن سرویس پیامک
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ReceiptBatch;
use Morilog\Jalali\Jalalian; // <--- این خط را حتماً اضافه کنید

use App\Models\SmsLog; // اضافه شدن مدل لاگ پیامک


class ReceiptController extends Controller
{

    protected $smsService;

    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * متد هوشمند برای پیدا کردن اولین جای خالی (Gap)
     */
    private function findFirstAvailableSlot($userId)
    {
        $batches = ReceiptBatch::where('user_id', $userId)
            ->orderBy('start_number', 'asc')
            ->get();

        foreach ($batches as $batch) {
            $existingSerials = Receipt::where('receipt_batch_id', $batch->id)
                ->pluck('serial_number')
                ->toArray();

            for ($i = $batch->start_number; $i <= $batch->end_number; $i++) {
                if (!in_array($i, $existingSerials)) {
                    return [
                        'batch' => $batch,
                        'serial' => $i
                    ];
                }
            }
        }

        return null;
    }

    public function index()
    {
        $receipts = Receipt::where('user_id', Auth::id())->latest()->paginate(10);
        return view('employee.receipts.index', compact('receipts'));
    }

    public function create()
    {
        $slot = $this->findFirstAvailableSlot(Auth::id());

        if (!$slot) {
            return redirect()->route('employee.batches.index')
                ->with('error', 'ظرفیت تمام دسته‌های شما تکمیل شده است. لطفاً برای صدور فیش، یک دسته جدید تعریف کنید.');
        }

        $nextSerialInfo = number_format($slot['serial']);

        return view('employee.receipts.create', compact('nextSerialInfo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'donor_name'       => 'required|string|max:191',
            'amount'           => 'required',
            'help_type'        => 'required|string',
            'help_type_detail' => 'nullable|required_if:help_type,سایر',
            'payment_type'     => 'required',
            'donor_mobile'     => 'nullable',
            'receipt_date'     => 'nullable|string', // فیلد تاریخ شمسی
        ], [
            'help_type_detail.required_if' => 'لطفاً نوع دقیق کمک (زیرمجموعه سایر) را انتخاب کنید.',
        ]);

        $finalHelpType = $request->help_type === 'سایر' ? $request->help_type_detail : $request->help_type;
        $mobile = $request->donor_mobile ? $this->convertPersianNumbers($request->donor_mobile) : null;

        // استانداردسازی مبلغ بدون ضرب در 10 (کاربر مستقیما ریال وارد میکند)
        $amountCleaned = str_replace(',', '', $request->amount);
        $amountCleaned = $this->convertPersianNumbers($amountCleaned);
        $amountRials = (int)$amountCleaned;

        // تبدیل تاریخ شمسی به میلادی
        $receiptDate = now();
        if ($request->filled('receipt_date')) {
            try {
                $persianDate = $this->convertPersianNumbers($request->receipt_date);
                $dateParts = explode('/', $persianDate);
                if (count($dateParts) == 3) {
                    $receiptDate = (new Jalalian((int)$dateParts[0], (int)$dateParts[1], (int)$dateParts[2]))->toCarbon();
                }
            } catch (\Exception $e) {
                Log::error("Error parsing Jalali date: " . $e->getMessage());
                // در صورت خطا همان now() استفاده می‌شود
            }
        }

        DB::beginTransaction();
        try {
            $slot = $this->findFirstAvailableSlot(Auth::id());

            if (!$slot) {
                throw new \Exception("ظرفیت تکمیل شده است. لطفاً دسته جدید تعریف کنید.");
            }

            $batch = $slot['batch'];
            $serialNumber = $slot['serial'];

            $receipt = new Receipt();
            $receipt->user_id          = Auth::id();
            $receipt->receipt_batch_id = $batch->id;
            $receipt->serial_number    = $serialNumber;
            $receipt->receipt_date     = $receiptDate; // تاریخ تبدیل شده
            $receipt->donor_name       = $request->donor_name;
            $receipt->donor_mobile     = $mobile;
            $receipt->amount_rials     = $amountRials;
            $receipt->amount_words     = $request->amount_words;
            $receipt->help_type        = $finalHelpType;
            $receipt->payment_type     = $request->payment_type;
            $receipt->description      = $request->description;
            $receipt->save();

            if ($batch->status == 'finished') {
                $batch->status = 'active';
            }

            if ($serialNumber >= $batch->current_number) {
                $batch->current_number = $serialNumber + 1;
            }

            $count = Receipt::where('receipt_batch_id', $batch->id)->count();
            $capacity = $batch->end_number - $batch->start_number + 1;

            if ($count >= $capacity) {
                $batch->status = 'finished';
            }

            $batch->save();

            DB::commit();

            // ارسال پیامک و ثبت لاگ
            if ($mobile) {
                $this->sendReceiptSms($receipt);
            }

            return redirect()->route('employee.receipts.create')
                ->with('success', "فیش با شماره سریال {$serialNumber} با موفقیت ثبت شد.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving receipt: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $receipt = Receipt::where('user_id', Auth::id())->findOrFail($id);
        return view('employee.receipts.edit', compact('receipt'));
    }

    public function update(Request $request, $id)
    {
        $receipt = Receipt::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'donor_name'       => 'required|string|max:191',
            'amount'           => 'required',
            'help_type'        => 'required|string',
            'help_type_detail' => 'nullable|required_if:help_type,سایر',
            'payment_type'     => 'required',
            'donor_mobile'     => 'nullable',
        ]);

        $finalHelpType = $request->help_type === 'سایر' ? $request->help_type_detail : $request->help_type;
        $mobile = $request->donor_mobile ? $this->convertPersianNumbers($request->donor_mobile) : null;

        // استانداردسازی مبلغ بدون ضرب در 10 (ورودی مستقیماً ریال است)
        $amountCleaned = str_replace(',', '', $request->amount);
        $amountCleaned = $this->convertPersianNumbers($amountCleaned);
        $amountRials = (int)$amountCleaned;

        $receipt->update([
            'donor_name'   => $request->donor_name,
            'amount_rials' => $amountRials,
            'help_type'    => $finalHelpType,
            'payment_type' => $request->payment_type,
            'donor_mobile' => $mobile,
            'description'  => $request->description,
        ]);

        // ارسال پیامک پس از ویرایش و ثبت لاگ
        if ($mobile) {
            $this->sendReceiptSms($receipt);
        }

        return redirect()->route('employee.batches.receipts', $receipt->receipt_batch_id)
            ->with('success', 'فیش ویرایش شد و پیامک جدید ارسال گردید.');
    }

    public function destroy($id)
    {
        $receipt = Receipt::where('user_id', Auth::id())->findOrFail($id);
        $batch = ReceiptBatch::findOrFail($receipt->receipt_batch_id);

        $receipt->delete();

        if ($batch->status === 'finished') {
            $batch->update(['status' => 'active']);
        }

        return redirect()->route('employee.batches.receipts', $batch->id)
            ->with('success', 'فیش ابطال شد و شماره سریال برای استفاده مجدد آزاد گردید.');
    }

    /**
     * متد ارسال پیامک همراه با سیستم ثبت لاگ جامع
     */
    private function sendReceiptSms($receipt)
    {
        // فرمت کردن مبلغ و تاریخ برای متن پیامک
        $amountFormatted = number_format($receipt->amount_rials);
        $date = function_exists('jdate') ? jdate($receipt->receipt_date)->format('Y/m/d') : $receipt->receipt_date->format('Y-m-d');

        $message = "نیکوکار گرامی {$receipt->donor_name} با سلام، بابت واریز مبلغ {$amountFormatted} ریال در تاریخ {$date} به حساب موسسه خیریه دارالاحسان از شما سپاسگزاریم.";

        try {
            // ارسال پیامک از طریق سرویس
            $this->smsService->sendOneToMany($message, [$receipt->donor_mobile]);

            // لاگ پیامک در وضعیت موفق
            SmsLog::create([
                'user_id'    => Auth::id(),
                'donor_name' => $receipt->donor_name,
                'mobile'     => $receipt->donor_mobile,
                'message'    => $message,
                'type'       => 'صدور فیش',
                'status'     => 'sent',
            ]);
        } catch (\Exception $e) {
            Log::error("SMS Failed: " . $e->getMessage());

            // لاگ پیامک در وضعیت خطا
            SmsLog::create([
                'user_id'       => Auth::id(),
                'donor_name'    => $receipt->donor_name,
                'mobile'        => $receipt->donor_mobile,
                'message'       => $message,
                'type'          => 'صدور فیش',
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function convertPersianNumbers($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $num = range(0, 9);
        $converted = str_replace($persian, $num, $string);
        return str_replace($arabic, $num, $converted);
    }
  
}
