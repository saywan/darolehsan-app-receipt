@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                {{-- هدر کارت: عنوان + جستجو + دکمه‌ها --}}
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #f0f0f0; border-radius: 15px 15px 0 0;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                        {{-- عنوان --}}
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-layers-half me-2 text-primary"></i>لیست دسته‌بندی‌های قبض
                        </h5>

                        <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto align-items-center">
                            {{-- فرم جستجو --}}
                            <form action="{{ route('employee.batches.index') }}" method="GET" class="d-flex flex-grow-1 w-100">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control bg-light border-start-0"
                                           placeholder="جستجو (ID، شماره، وضعیت)..."
                                           value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">بیاب</button>
                                </div>
                            </form>

                            <div class="d-flex gap-2">
                                {{-- دکمه 1: صدور فیش جدید (لینک به فرم هوشمند صدور فیش) --}}
                                <a href="{{ route('employee.receipts.create') }}" class="btn btn-warning fw-bold shadow-sm d-flex align-items-center btn-pulse text-dark">
                                    <i class="bi bi-receipt me-2"></i>صدور فیش
                                </a>

                                {{-- دکمه 2: تعریف دسته جدید --}}
                                <a href="{{ route('employee.batches.create') }}" class="btn btn-primary fw-bold shadow-sm d-flex align-items-center">
                                    <i class="bi bi-plus-lg me-2"></i>دسته جدید
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- نمایش پیام‌های سیستم --}}
                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4 d-flex align-items-center shadow-sm border-0 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger rounded-3 mb-4 d-flex align-items-center shadow-sm border-0 bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 text-secondary">ID</th>
                                    <th class="py-3 text-secondary">بازه شماره سریال</th>
                                    <th class="py-3 text-secondary">وضعیت</th>
                                    <th class="py-3 text-secondary" style="width: 25%;">آمار پیشرفت</th>
                                    <th class="py-3 text-secondary text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                    <tr>
                                        {{-- ID --}}
                                        <td class="fw-bold text-muted">#{{ $batch->id }}</td>

                                        {{-- بازه شماره --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border px-2">{{ number_format($batch->start_number) }}</span>
                                                <i class="bi bi-arrow-left-short text-muted mx-1"></i>
                                                <span class="badge bg-light text-dark border px-2">{{ number_format($batch->end_number) }}</span>
                                            </div>
                                        </td>

                                        {{-- وضعیت --}}
                                        <td>
                                            @if($batch->status == 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 0.5rem; height: 0.5rem;"></span> فعال
                                                </span>
                                            @elseif($batch->status == 'finished')
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">
                                                    <i class="bi bi-check-all me-1"></i> تمام شده
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">{{ $batch->status }}</span>
                                            @endif
                                        </td>

                                        {{-- آمار پیشرفت (محاسبه دقیق) --}}
                                        <td>
                                            @php
                                                $total = $batch->end_number - $batch->start_number + 1;
                                                // استفاده از تعداد واقعی فیش‌های موجود در دیتابیس (ارسال شده از کنترلر)
                                                // اگر متغیر receipts_count وجود نداشت (برای اطمینان) 0 در نظر بگیر
                                                $actualCount = $batch->receipts_count ?? 0;

                                                $percent = ($total > 0) ? round(($actualCount / $total) * 100) : 0;

                                                // تعیین رنگ بر اساس وضعیت
                                                $progressColor = match($batch->status) {
                                                    'finished' => 'bg-secondary', // خاکستری برای تمام شده
                                                    'active' => 'bg-success',     // سبز برای فعال
                                                    default => 'bg-warning'
                                                };
                                            @endphp

                                            <div class="d-flex align-items-center mb-1">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar {{ $progressColor }} progress-bar-striped {{ $batch->status == 'active' ? 'progress-bar-animated' : '' }}"
                                                         role="progressbar"
                                                         style="width: {{ $percent }}%"></div>
                                                </div>
                                                <span class="ms-2 small fw-bold {{ $batch->status == 'active' ? 'text-success' : 'text-muted' }}">{{ $percent }}%</span>
                                            </div>

                                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                                <span>صادر شده: <strong>{{ number_format($actualCount) }}</strong></span>
                                                <span>کل: {{ number_format($total) }}</span>
                                            </div>
                                        </td>

                                        {{-- عملیات --}}
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- مشاهده ریز فیش‌ها --}}
                                                <a href="{{ route('employee.batches.receipts', $batch->id) }}"
                                                   class="btn btn-sm btn-info text-white shadow-sm d-flex align-items-center"
                                                   title="مشاهده ریز فیش‌ها">
                                                    <i class="bi bi-list-ul me-1"></i> ریز فیش‌ها
                                                </a>

                                                {{-- ویرایش دسته --}}
                                                {{-- <a href="{{ route('employee.batches.edit', $batch->id) }}"
                                                   class="btn btn-sm btn-light border text-secondary shadow-sm"
                                                   title="ویرایش دسته">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a> --}}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bi bi-inbox fs-1 mb-2 opacity-25"></i>
                                                <p>هیچ دسته‌ای یافت نشد.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $batches->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* انیمیشن چشمک زن برای دکمه صدور فیش */
    .btn-pulse {
        animation: pulse-yellow 2s infinite;
    }

    @keyframes pulse-yellow {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }
</style>
@endsection
