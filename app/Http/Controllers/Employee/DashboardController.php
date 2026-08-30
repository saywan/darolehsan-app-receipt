<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Receipt;
use App\Models\ReceiptBatch;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta; // اطمینان از ایمپورت کلاس ورتا


class DashboardController extends Controller
{
    //

    public function index()
    {
        $user = Auth::user();

        // 1. آمار کلی (کارت‌های بالای صفحه)
        $today = Carbon::today();

        $stats = [
            'total_amount'   => Receipt::where('user_id', $user->id)->sum('amount_rials'),
            'today_count'    => Receipt::where('user_id', $user->id)->whereDate('created_at', $today)->count(),
            'today_amount'   => Receipt::where('user_id', $user->id)->whereDate('created_at', $today)->sum('amount_rials'),
            'total_receipts' => Receipt::where('user_id', $user->id)->count(),
        ];

        // 2. وضعیت دسته چک فعال
        $activeBatch = ReceiptBatch::where('user_id', $user->id)
            ->where('status', 'active')
            ->withCount('receipts') // شمارش تعداد فیش‌های صادر شده واقعی
            ->first();

        $batchProgress = 0;
        if ($activeBatch) {
            // محاسبه پیشرفت بر اساس ظرفیت کل (پایان - شروع + 1)
            $totalCapacity = ($activeBatch->end_number - $activeBatch->start_number) + 1;
            if ($totalCapacity > 0) {
                $batchProgress = round(($activeBatch->receipts_count / $totalCapacity) * 100);
            }
        }

        // 3. داده‌های نمودار (7 روز گذشته با تاریخ کامل شمسی)
        $chartLabels = [];
        $chartValues = [];

        for ($i = 6; $i >= 0; $i--) {
            // تاریخ میلادی برای کوئری گرفتن
            $date = Carbon::now()->subDays($i);

            // تبدیل تاریخ به شمسی کامل (مثلاً: 1403/11/29)
            // Y: سال چهار رقمی، m: ماه دو رقمی، d: روز دو رقمی
            $jalaliDate = verta($date)->format('Y/m/d');

            // گرفتن آمار آن روز خاص
            $count = Receipt::where('user_id', $user->id)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();

            $chartLabels[] = $jalaliDate; // لیبل شمسی کامل
            $chartValues[] = $count;      // تعداد فیش
        }

        // 4. لیست 5 تراکنش آخر
        $recentReceipts = Receipt::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('employee.dashboard.index', compact(
            'user',
            'stats',
            'activeBatch',
            'batchProgress',
            'chartLabels',
            'chartValues',
            'recentReceipts'
        ));
    }
}
