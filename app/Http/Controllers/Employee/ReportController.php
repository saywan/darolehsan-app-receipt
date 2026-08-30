<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Carbon\Carbon;
// use Maatwebsite\Excel\Facades\Excel; // در صورت داشتن خروجی اکسل از کامنت خارج کنید
// use App\Exports\Employee\ReceiptsExport; // کلاس اکسپورت شما
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. ساخت کوئری پایه
        $query = Receipt::query();

        // 2. اعمال فیلترهای پیشرفته
        $this->applyFilters($query, $request);

        // 3. کلون کردن کوئری برای گرفتن آمارهای کلی (بدون تاثیر صفحه‌بندی)
        $statsQuery = clone $query;
        $topDonorsQuery = clone $query;
        $helpTypesQuery = clone $query;

        // 4. محاسبات آماری (تبدیل ریال به تومان در ویو انجام می‌شود)
        $totalAmountRials = $statsQuery->sum('amount_rials');
        $totalCount = $statsQuery->count();
        $maxDonationRials = $statsQuery->max('amount_rials');
        $avgDonationRials = $statsQuery->avg('amount_rials');

        // 5. استخراج 10 خیر برتر بر اساس مبالغ واریزی در بازه فیلتر شده
        $topDonors = $topDonorsQuery->selectRaw('donor_name, donor_mobile, SUM(amount_rials) as total_donated, COUNT(id) as donation_count')
            ->whereNotNull('donor_name')
            ->groupBy('donor_name', 'donor_mobile')
            ->orderByDesc('total_donated')
            ->limit(10)
            ->get();

        // 6. آمار توصیفی تفکیک بر اساس نوع کمک (زکات، صدقه و ...)
        $helpTypeStats = $helpTypesQuery->selectRaw('help_type, SUM(amount_rials) as total_amount, COUNT(id) as count')
            ->groupBy('help_type')
            ->orderByDesc('total_amount')
            ->get();

        // 7. دریافت لیست تراکنش‌ها با صفحه‌بندی
        $receipts = $query->orderBy('receipt_date', 'desc')->paginate(20)->withQueryString();

        return view('employee.reports.index', compact(
            'receipts',
            'totalAmountRials',
            'totalCount',
            'maxDonationRials',
            'avgDonationRials',
            'topDonors',
            'helpTypeStats'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = Receipt::query();
        $this->applyFilters($query, $request);
        
        $receipts = $query->orderBy('receipt_date', 'desc')->get();
        $totalAmountRials = $query->sum('amount_rials');
        
        $filterDescription = $this->getFilterDescription($request);

        $pdf = Pdf::loadView('employee.reports.pdf', compact('receipts', 'totalAmountRials', 'filterDescription'));
        
        return $pdf->download('financial_report_' . Carbon::now()->format('Y_m_d_H_i') . '.pdf');
    }

    // متد اعمال فیلترها (برای جلوگیری از تکرار کد)
    private function applyFilters($query, $request)
    {
        // فیلتر جستجوی متنی نام و موبایل و سریال
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_mobile', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('doc_code', 'like', "%{$search}%");
            });
        }

        // فیلتر نوع کمک
        if ($request->filled('help_type')) {
            $query->where('help_type', $request->help_type);
        }

        // فیلتر نوع پرداخت
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // فیلترهای زمانی هوشمند (روزانه، هفتگی و ...) بر اساس تاریخ رسید
        if ($request->filled('time_filter')) {
            switch ($request->time_filter) {
                case 'today':
                    $query->whereDate('receipt_date', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('receipt_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('receipt_date', Carbon::now()->month)
                          ->whereYear('receipt_date', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('receipt_date', Carbon::now()->year);
                    break;
            }
        } else {
            // فیلتر دستی تاریخ
            if ($request->filled('start_date')) {
                $query->whereDate('receipt_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('receipt_date', '<=', $request->end_date);
            }
        }
    }

    // تولید متن توصیفی برای هدر فایل PDF
    private function getFilterDescription($request)
    {
        $desc = [];
        if ($request->filled('time_filter')) {
            $filters = ['today' => 'امروز', 'week' => 'این هفته', 'month' => 'این ماه', 'year' => 'امسال'];
            $desc[] = "بازه زمانی: " . ($filters[$request->time_filter] ?? 'نامشخص');
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            $desc[] = "از تاریخ " . ($request->start_date ?? 'ابتدا') . " تا " . ($request->end_date ?? 'اکنون');
        }
        if ($request->filled('help_type')) $desc[] = "نوع کمک: " . $request->help_type;
        
        return count($desc) > 0 ? implode(' | ', $desc) : 'تمام رکوردها (بدون فیلتر)';
    }
}