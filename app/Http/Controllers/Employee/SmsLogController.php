<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use App\Services\NegarSmsService;
use Illuminate\Support\Facades\Log;

class SmsLogController extends Controller
{

    protected $smsService;

    // تزریق سرویس پیامک به کنترلر
    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش لیست لاگ‌های پیامک
     */
    public function index(Request $request)
    {
        $query = SmsLog::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mobile', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('employee.sms_logs.index', compact('logs'));
    }

   /**
     * عملیات ارسال مجدد پیامک
     */
    public function resend(SmsLog $log)
    {
        // جلوگیری از ارسال مجدد پیامکی که وضعیت آن موفق است
        if ($log->status === 'sent') {
            return back()->with('error', 'این پیامک قبلاً با موفقیت ارسال شده است و نیازی به ارسال مجدد ندارد.');
        }

        try {
            // تلاش برای ارسال مجدد پیامک با استفاده از سرویس نگار
            $this->smsService->sendOneToMany($log->message, [$log->mobile]);

            // بروزرسانی لاگ در صورت موفقیت
            $log->update([
                'status' => 'sent',
                'error_message' => null, // پاک کردن خطای قبلی در صورت وجود
                'updated_at' => now(),   // بروزرسانی زمان آخرین تلاش
            ]);

            return back()->with('success', 'پیامک با موفقیت مجدداً ارسال شد.');

        } catch (\Exception $e) {
            Log::error("SMS Resend Failed for Log ID {$log->id}: " . $e->getMessage());

            // بروزرسانی لاگ با خطای جدید در صورت شکست
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'updated_at' => now(),
            ]);

            return back()->with('error', 'ارسال مجدد پیامک با خطا مواجه شد. در ستون وضعیت، خطا را بررسی کنید.');
        }
    }
}
