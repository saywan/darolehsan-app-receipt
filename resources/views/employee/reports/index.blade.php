@extends('layouts.panel')

@section('title', 'داشبورد گزارشات مالی')
@section('header_title', 'گزارشات حرفه‌ای و تحلیل تراکنش‌ها')

@section('content')
<div class="container-fluid p-0">

    <!-- 1. کارت‌های آماری کلیدی (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 border-end border-5 border-primary h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted mb-2 fw-bold">مجموع درآمدهای این گزارش</h6>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($totalAmountRials / 10) }} <small class="fs-6 text-muted">تومان</small></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 border-end border-5 border-success h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted mb-2 fw-bold">تعداد کل رسیدها</h6>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($totalCount) }} <small class="fs-6 text-muted">مورد</small></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 border-end border-5 border-warning h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted mb-2 fw-bold">بیشترین مبلغ یک رسید</h6>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($maxDonationRials / 10) }} <small class="fs-6 text-muted">تومان</small></h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 border-end border-5 border-info h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted mb-2 fw-bold">میانگین مبالغ اهدایی</h6>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($avgDonationRials / 10) }} <small class="fs-6 text-muted">تومان</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. بخش گزارشات توصیفی (10 خیر برتر + تفکیک نوع کمک) -->
    <div class="row g-4 mb-4">
        <!-- لیست 10 خیر برتر -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i> 10 خیّر برتر (بیشترین پرداختی در این فیلتر)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle">
                            <thead class="text-muted border-bottom">
                                <tr>
                                    <th>رتبه</th>
                                    <th>نام خیّر</th>
                                    <th>موبایل</th>
                                    <th>تعداد کمک</th>
                                    <th class="text-end">مجموع واریزی (تومان)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDonors as $index => $donor)
                                <tr>
                                    <td>
                                        @if($index == 0) <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-star-fill"></i></span>
                                        @elseif($index == 1) <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-star-fill"></i></span>
                                        @elseif($index == 2) <span class="badge rounded-circle p-2" style="background-color: #cd7f32;"><i class="bi bi-star-fill"></i></span>
                                        @else <span class="text-muted fw-bold ms-2">{{ $index + 1 }}</span> @endif
                                    </td>
                                    <td class="fw-bold">{{ $donor->donor_name }}</td>
                                    <td class="text-muted">{{ $donor->donor_mobile ?? '---' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $donor->donation_count }} رسید</span></td>
                                    <td class="text-end fw-bold text-success">{{ number_format($donor->total_donated / 10) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">داده‌ای یافت نشد</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- آمار تفکیک نوع کمک -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i> تفکیک سرفصل‌های درآمدی</h6>
                </div>
                <div class="card-body">
                    @forelse($helpTypeStats as $stat)
                        @php 
                            $percentage = $totalAmountRials > 0 ? ($stat->total_amount / $totalAmountRials) * 100 : 0;
                            // تولید رنگ تصادفی بر اساس نام سرفصل
                            $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                            $colorClass = $colors[$loop->index % count($colors)];
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold fs-6">{{ $stat->help_type ?? 'نامشخص' }}</span>
                                <span class="text-muted small">{{ number_format($stat->total_amount / 10) }} تومان</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">{{ number_format($percentage, 1) }}% از کل - شامل {{ $stat->count }} رسید</small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">داده‌ای برای نمایش وجود ندارد</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 3. فرم فیلترهای پیشرفته -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <form action="{{ route('employee.reports.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">جستجو (نام، موبایل، سریال)</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">بازه‌های زمانی آماده</label>
                    <select name="time_filter" class="form-select form-select-sm">
                        <option value="">انتخاب کنید...</option>
                        <option value="today" {{ request('time_filter') == 'today' ? 'selected' : '' }}>درآمدهای امروز</option>
                        <option value="week" {{ request('time_filter') == 'week' ? 'selected' : '' }}>هفته جاری</option>
                        <option value="month" {{ request('time_filter') == 'month' ? 'selected' : '' }}>ماه جاری</option>
                        <option value="year" {{ request('time_filter') == 'year' ? 'selected' : '' }}>سال جاری</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">از تاریخ (دستی)</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">تا تاریخ (دستی)</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold"><i class="bi bi-funnel"></i> اعمال فیلتر</button>
                    <a href="{{ route('employee.reports.index') }}" class="btn btn-light btn-sm border" title="پاک کردن فیلترها"><i class="bi bi-eraser"></i></a>
                </div>
            </form>
        </div>
        
        <!-- هدر جدول و دکمه‌های خروجی -->
        <div class="card-header bg-light border-top border-bottom-0 d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>لیست ریز تراکنش‌ها</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('employee.reports.pdf', request()->query()) }}" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="bi bi-file-pdf"></i> چاپ PDF گزارش
                </a>
                {{-- در صورت نیاز به اکسل این خط را از کامنت خارج کنید --}}
                {{-- <a href="{{ route('employee.reports.excel', request()->query()) }}" class="btn btn-success btn-sm rounded-pill fw-bold px-3">
                    <i class="bi bi-file-excel"></i> خروجی اکسل
                </a> --}}
            </div>
        </div>

        <!-- 4. جدول داده‌ها -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="px-4">سریال / کد مدرک</th>
                            <th>نام خیر</th>
                            <th>موبایل</th>
                            <th>نوع کمک / پرداخت</th>
                            <th>تاریخ رسید</th>
                            <th class="text-end px-4">مبلغ (تومان)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td class="px-4">
                                    <span class="text-danger fw-bold">{{ $receipt->serial_number ?? '---' }}</span><br>
                                    <span class="text-muted small">کد: {{ $receipt->doc_code ?? '---' }}</span>
                                </td>
                                <td class="fw-bold">{{ $receipt->donor_name ?? 'نامشخص' }}</td>
                                <td class="text-muted">{{ $receipt->donor_mobile ?? '---' }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $receipt->help_type ?? 'نامشخص' }}</span>
                                    <span class="badge bg-light text-dark border">{{ $receipt->payment_type ?? 'نامشخص' }}</span>
                                </td>
                                <td dir="ltr" class="text-end text-muted">
                                    {{ $receipt->receipt_date ? \Carbon\Carbon::parse($receipt->receipt_date)->format('Y/m/d H:i') : '---' }}
                                </td>
                                <td class="text-success fw-bold text-end px-4">
                                    {{ number_format($receipt->amount_rials / 10) }} تومان
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    تراکنشی با این مشخصات در سیستم یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- صفحه‌بندی -->
        <div class="card-footer bg-white border-top py-3">
            {{ $receipts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
