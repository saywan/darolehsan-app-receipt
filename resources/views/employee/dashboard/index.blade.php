@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    
    {{-- بخش هدر و خوش‌آمدگویی --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">داشبورد همکاران</h4>
            <p class="text-muted mb-0 small">خلاصه عملکرد و وضعیت صدور فیش‌ها</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-secondary border shadow-sm p-2 px-3 rounded-pill">
                <i class="bi bi-calendar-event me-1"></i>
                {{ \Carbon\Carbon::now()->format('Y/m/d') }}
            </span>
        </div>
    </div>

    {{-- ردیف کارت‌های آماری --}}
    <div class="row g-3 mb-4">
        <!-- کارت 1: جمع کل مبلغ -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3 ms-3">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">جمع مبالغ دریافتی (کل)</p>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($stats['total_amount']) }} <span class="fs-6 text-muted fw-normal">ریال</span></h5>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 px-3 border-0">
                    <small class="text-success fw-bold"><i class="bi bi-arrow-up-short"></i> عملکرد کلی</small>
                </div>
            </div>
        </div>

        <!-- کارت 2: تعداد فیش‌های امروز -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3 ms-3">
                            <i class="bi bi-receipt-cutoff fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">فیش‌های صادر شده (امروز)</p>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($stats['today_count']) }} <span class="fs-6 text-muted fw-normal">عدد</span></h5>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 px-3 border-0">
                    <small class="text-primary fw-bold">
                        مبلغ امروز: {{ number_format($stats['today_amount']) }} ریال
                    </small>
                </div>
            </div>
        </div>

        <!-- کارت 3: وضعیت دسته چک فعال -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                <div class="card-body p-3">
                    @if($activeBatch)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <p class="text-muted small mb-1">دسته چک فعال</p>
                                <h6 class="fw-bold mb-0">سری {{ $activeBatch->start_number }} تا {{ $activeBatch->end_number }}</h6>
                            </div>
                            <span class="badge bg-warning text-dark">{{ $activeBatch->id }}#</span>
                        </div>
                        {{-- نوار پیشرفت --}}
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $batchProgress }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small">
                            <span class="text-muted">پیشرفت: {{ $batchProgress }}%</span>
                            <span class="text-dark fw-bold">{{ $activeBatch->receipts_count }} صادر شده</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <small><i class="bi bi-exclamation-circle me-1"></i>دسته چک فعالی ندارید</small>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light py-2 px-3 border-0 text-center">
                    @if($activeBatch)
                        <a href="{{ route('employee.receipts.create') }}" class="text-decoration-none small fw-bold text-warning text-dark-hover stretched-link">
                            صدور فیش جدید <i class="bi bi-arrow-left"></i>
                        </a>
                    @else
                         <a href="{{ route('employee.batches.create') }}" class="text-decoration-none small fw-bold text-primary stretched-link">
                            دریافت دسته چک <i class="bi bi-plus-circle"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- کارت 4: کل فیش‌ها -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3 ms-3">
                            <i class="bi bi-files fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">کل فیش‌های صادر شده</p>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($stats['total_receipts']) }} <span class="fs-6 text-muted fw-normal">عدد</span></h5>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 px-3 border-0">
                    <small class="text-muted">از ابتدای همکاری</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ردیف نمودار و آخرین فعالیت‌ها --}}
    <div class="row g-3">
        <!-- نمودار فعالیت -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>روند صدور فیش (۷ روز اخیر)</h6>
                </div>
                <div class="card-body">
                    <canvas id="receiptsChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- لیست آخرین فیش‌ها -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>آخرین فیش‌ها</h6>
                    {{-- اصلاح لینک: تغییر به لیست دسته‌ها --}}
                    <a href="{{ route('employee.batches.index') }}" class="btn btn-sm btn-light text-primary small">مشاهده همه</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentReceipts as $receipt)
                            <div class="list-group-item border-0 border-bottom px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 small fw-bold">{{ $receipt->donor_name }}</h6>
                                        <div class="small text-muted">سریال: <span class="font-monospace">{{ $receipt->serial_number }}</span></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success small">{{ number_format($receipt->amount_rials) }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.75rem;">
                                            {{ \Carbon\Carbon::parse($receipt->created_at)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-25"></i>
                                <p class="small mt-2">هنوز فیشی صادر نشده است.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- اسکریپت نمودار --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('receiptsChart').getContext('2d');
        
        const labels = @json($chartLabels);
        const data = @json($chartValues);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'تعداد فیش',
                    data: data,
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0d6efd',
                    pointRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .icon-box {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .text-dark-hover:hover {
        color: #000 !important;
    }
</style>
@endsection
