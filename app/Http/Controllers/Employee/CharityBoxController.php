<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CharityBox;
use App\Models\BoxAllocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Hekmatinasser\Verta\Verta;
use App\Models\ReceiptBatch; // اضافه شد
use App\Models\Receipt;      // اضافه شد
use App\Services\NegarSmsService; // مسیر فایل سرویس پیامک خود را چک کنید

use App\Models\SmsLog; // <-- مدل لاگ پیامک اضافه شد

use Illuminate\Support\Facades\Log;


class CharityBoxController extends Controller
{
    protected $smsService;

    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش لیست صندوق‌های فعال و بایگانی
     */
    public function index(Request $request)
    {
        $isArchived = $request->query('status') === 'archived';

        $query = BoxAllocation::with('charityBox');

        if ($isArchived) {
            $query->whereNotNull('collected_at');
        } else {
            $query->whereNull('collected_at');
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('applicant_mobile', 'like', "%{$search}%")
                    ->orWhere('receiver_phone', 'like', "%{$search}%")
                    ->orWhere('applicant_national_code', 'like', "%{$search}%")
                    ->orWhereHas('charityBox', function ($boxQuery) use ($search) {
                        $boxQuery->where('code', 'like', "%{$search}%");
                    });
            });
        }

        $allocations = $query->latest()->get();

        if ($request->ajax()) {
            return view('employee.boxes.partials.table_rows', compact('allocations', 'isArchived'))->render();
        }

        return view('employee.boxes.index', compact('allocations', 'isArchived'));
    }

    /**
     * نمایش فرم تحویل صندوق جدید
     */
    public function create()
    {
        $availableBoxes = CharityBox::where('status', 'available')->get();
        return view('employee.boxes.create', compact('availableBoxes'));
    }

    /**
     * ثبت تحویل صندوق در دیتابیس و ارسال پیامک
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|in:plastic,glass',
            'code'             => 'required|string|max:255',
            'applicant_name'   => 'required|string|max:255',
            'applicant_mobile' => 'required|string|max:11|regex:/^09[0-9]{9}$/',
            'applicant_national_code' => 'nullable|string|max:10',
            'applicant_address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $box = CharityBox::where('code', $request->code)
                ->where('type', $request->type)
                ->first();

            if ($box) {
                if ($box->status !== 'available') {
                    $typeFa = $request->type === 'plastic' ? 'پلاستیکی' : 'شیشه‌ای';
                    return back()->withInput()->with('error', "یک صندوق {$typeFa} با کد {$request->code} قبلاً ثبت شده و در حال حاضر دست شخص دیگری است.");
                }
            } else {
                $box = CharityBox::create([
                    'code'   => $request->code,
                    'type'   => $request->type,
                    'status' => 'available'
                ]);
            }

            BoxAllocation::create([
                'box_id'                  => $box->id,
                'user_id'                 => Auth::id(),
                'applicant_name'          => $request->applicant_name,
                'applicant_national_code' => $request->applicant_national_code,
                'applicant_mobile'        => $request->applicant_mobile,
                'applicant_address'       => $request->applicant_address,
                'assigned_at'             => now(),
                'status'                  => 'active'
            ]);

            $box->update(['status' => 'assigned']);

            DB::commit();

            // ارسال و لاگ پیامک
            $smsStatus = '';
            $date = verta()->format('Y/m/d');

            $message = "نیکوکار گرامی جناب/سرکار {$request->applicant_name}\n" .
                "صندوق/قلک شماره {$box->code} در تاریخ {$date} با موفقیت به شما تحویل داده شد.\n" .
                "اجرکم عندالله - خیریه دارالاحسان";

            try {
                $this->smsService->sendOneToMany($message, [$request->applicant_mobile]);

                SmsLog::create([
                    'receiver_name' => $request->applicant_name,
                    'mobile'        => $request->applicant_mobile,
                    'message'       => $message,
                    'status'        => 'sent',
                    'type'          => 'box_assigned'
                ]);

                $smsStatus = ' و پیامک اطلاع‌رسانی ارسال شد.';
            } catch (\Exception $e) {
                Log::error('خطا در ارسال پیامک تحویل صندوق: ' . $e->getMessage());
                SmsLog::create([
                    'receiver_name' => $request->applicant_name,
                    'mobile'        => $request->applicant_mobile,
                    'message'       => $message,
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'type'          => 'box_assigned'
                ]);
                $smsStatus = ' اما در ارسال پیامک خطایی رخ داد.';
            }

            return redirect()->route('employee.boxes.index')
                ->with('success', 'صندوق با موفقیت ثبت و تحویل داده شد' . $smsStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در ثبت صندوق: ' . $e->getMessage());
            return back()->withInput()->with('error', 'خطای سیستم: ' . $e->getMessage());
        }
    }

    /**
     * نمایش فرم تخلیه و صدور فیش (collect.blade.php)
     */
    public function edit(BoxAllocation $boxAllocation)
    {
        if ($boxAllocation->collected_at !== null) {
            return redirect()->route('employee.boxes.index')
                ->with('error', 'این صندوق قبلاً تخلیه و مبلغ آن ثبت شده است.');
        }

        // گرفتن دسته قبض فعال کارمند برای پاس دادن سریال به فرم
        $activeBatch = ReceiptBatch::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$activeBatch) {
            return redirect()->route('employee.boxes.index')
                ->with('error', 'شما دسته قبض فعالی ندارید. لطفاً برای تخلیه صندوق و صدور فیش، ابتدا یک دسته قبض فعال کنید.');
        }

        $nextSerialInfo = $activeBatch->receipt_prefix . '-' . $activeBatch->current_number;

        // توجه: نام ویو اینجا روی collect تنظیم شده است
        return view('employee.boxes.collect', compact('boxAllocation', 'nextSerialInfo'));
    }

    /**
     * ثبت مبلغ، صدور فیش از دسته قبض و تخلیه صندوق
     */
    public function update(Request $request, BoxAllocation $boxAllocation)
    {
        // جلوگیری از ثبت تکراری
        if ($boxAllocation->collected_at !== null) {
            return redirect()->route('employee.boxes.index')
                ->with('error', 'این صندوق قبلاً تخلیه شده است.');
        }

        // ولیدیشن متناسب با فیلدهای فرم جدید شما
        $request->validate([
            'amount_actual' => 'required|numeric|min:1000', // نام فیلد بر اساس فرم تغییر کرد، حداقل ۱۰۰۰ ریال
            'receipt_date'  => 'required|string',
            'payment_type'  => 'required|in:ماهانه,موردی',
            'donation_type' => 'required|string',
            'other_donation_type' => 'nullable|string|required_if:donation_type,سایر...',
        ]);

        DB::beginTransaction();
        try {
            // ۱. بررسی و قفل کردن دسته قبض فعال برای جلوگیری از Overdraw
            $activeBatch = ReceiptBatch::where('user_id', Auth::id())
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$activeBatch) {
                throw new \Exception('دسته قبض فعالی برای شما یافت نشد.');
            }

            if ($activeBatch->current_number > $activeBatch->end_number) {
                $activeBatch->update(['status' => 'completed']);
                throw new \Exception('دسته قبض فعلی شما تمام شده است. لطفاً دسته قبض جدید دریافت کنید.');
            }

            $receiptNumber = $activeBatch->current_number;
            $serialNumber = $activeBatch->receipt_prefix . '-' . $receiptNumber;

            // ۲. تبدیل تاریخ شمسی به میلادی استاندارد (با استفاده از سیستم Parse ورتا)
            try {
                $receiptDate = Verta::parse(str_replace('/', '-', $request->receipt_date))->datetime()->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception('فرمت تاریخ وارد شده نامعتبر است.');
            }

            $donationType = $request->donation_type === 'سایر...' ? $request->other_donation_type : $request->donation_type;

            // ۳. ایجاد رکورد فیش
            Receipt::create([
                'batch_id'         => $activeBatch->id,
                'user_id'          => Auth::id(),
                'donor_name'       => $boxAllocation->applicant_name,
                'donor_mobile'     => $request->mobile ?? $boxAllocation->applicant_mobile,
                'receipt_number'   => $receiptNumber,
                'serial_number'    => $serialNumber,
                'amount'           => $request->amount_actual,
                'payment_type'     => $request->payment_type,
                'donation_type'    => $donationType,
                'receipt_date'     => $receiptDate,
                'description'      => $request->description,
            ]);

            // ۴. آپدیت کردن دسته قبض
            $nextNumber = $receiptNumber + 1;
            $batchData = ['current_number' => $nextNumber];
            if ($nextNumber > $activeBatch->end_number) {
                $batchData['status'] = 'completed';
            }
            $activeBatch->update($batchData);

            // ۵. ثبت مبلغ و وضعیت تخلیه در تخصیص صندوق
            $boxAllocation->update([
                'amount'       => $request->amount_actual,
                'collected_at' => now(),
                'status'       => 'collected'
            ]);

            // ۶. تغییر وضعیت فیزیکی صندوق
            $box = $boxAllocation->charityBox;
            if ($box) {
                if ($box->type === 'plastic') {
                    $box->update(['status' => 'destroyed']);
                } elseif ($box->type === 'glass') {
                    $box->update(['status' => 'available']);
                }
            }

            DB::commit();

            // ================== ۷. ارسال پیامک با شماره فیش ==================
            $phone = $request->mobile ?? $boxAllocation->applicant_mobile;
            $name  = $boxAllocation->applicant_name;
            $smsStatus = '';

            if ($phone) {
                $date = verta()->format('Y/m/d');
                $amountFormatted = number_format($request->amount_actual);

                $message = "نیکوکار گرامی {$name}\n" .
                    "مبلغ {$amountFormatted} ریال از صندوق شما جمع‌آوری و فیش شماره {$receiptNumber} در تاریخ {$date} صادر شد.\n" .
                    "سپاس از همراهی شما - خیریه دارالاحسان";

                try {
                    $this->smsService->sendOneToMany($message, [$phone]);
                    SmsLog::create([
                        'receiver_name' => $name,
                        'mobile'        => $phone,
                        'message'       => $message,
                        'status'        => 'sent',
                        'type'          => 'box_collected'
                    ]);
                    $smsStatus = ' و پیامک رسید مبلغ حاوی شماره فیش به نیکوکار ارسال شد.';
                } catch (\Exception $e) {
                    Log::error('خطا در ارسال پیامک تخلیه صندوق: ' . $e->getMessage());
                    SmsLog::create([
                        'receiver_name' => $name,
                        'mobile'        => $phone,
                        'message'       => $message,
                        'status'        => 'failed',
                        'error_message' => $e->getMessage(),
                        'type'          => 'box_collected'
                    ]);
                    $smsStatus = ' اما ارسال پیامک با خطا مواجه شد.';
                }
            }
            // =========================================================

            return redirect()->route('employee.boxes.index')
                ->with('success', "تخلیه صندوق با موفقیت انجام و فیش شماره {$receiptNumber} صادر شد" . $smsStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در تخلیه صندوق و صدور فیش: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
