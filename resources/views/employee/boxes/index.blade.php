

@extends('layouts.panel')

@section('header_title', 'مدیریت صندوق و قلک')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">

        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

            <!-- تب‌های داینامیک (فعال و بایگانی) -->
            <div class="d-flex align-items-center gap-2 bg-light p-1" style="border-radius: 50px;">
                <a href="{{ route('employee.boxes.index') }}" class="text-decoration-none px-4 py-2 transition-all"
                   style="border-radius: 50px; font-size: 14px; font-weight: 500; {{ !$isArchived ? 'background-color: #EBF0FF; color: #5B4BFF;' : 'color: #6C757D;' }}">
                    صندوق‌های فعال
                </a>

                <a href="{{ route('employee.boxes.index', ['status' => 'archived']) }}" class="text-decoration-none px-4 py-2 transition-all"
                   style="border-radius: 50px; font-size: 14px; font-weight: 500; {{ $isArchived ? 'background-color: #EBF0FF; color: #5B4BFF;' : 'color: #6C757D;' }}">
                    بایگانی تخلیه شده‌ها
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="input-group" style="width: 320px;">
                    <input type="text" id="searchInput" class="form-control border-end-0 shadow-none bg-white" placeholder="جستجو (کد، نام یا موبایل)..." style="border-radius: 50px 0 0 50px; font-size: 13px; border-color: #E2E8F0; padding-right: 20px;">
                    <span class="input-group-text bg-white bg-transparent shadow-none" style="border-radius: 0 50px 50px 0; border-color: #E2E8F0; cursor: pointer;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>

                @if(!$isArchived)
                <a href="{{ route('employee.boxes.create') }}" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius: 50px; padding: 8px 20px; font-size: 14px;">
                    <i class="fas fa-plus"></i> تحویل صندوق جدید
                </a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle text-center mb-0" style="border-top: 1px solid #F1F5F9;">
                    <thead>
                        <tr>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">کد صندوق</th>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">نوع</th>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">تحویل گیرنده</th>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">شماره تماس</th>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">
                                {{ $isArchived ? 'تاریخ و مبلغ تخلیه' : 'تاریخ تحویل' }}
                            </th>
                            <th class="py-3 text-dark font-weight-bold" style="font-size: 14px; border-bottom: 1px solid #F1F5F9;">وضعیت / عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('employee.boxes.partials.table_rows', ['allocations' => $allocations, 'isArchived' => $isArchived])
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    let searchTimeout = null;
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');

    searchInput.addEventListener('keyup', function() {
        // حذف تایمر قبلی (جلوگیری از ارسال درخواست‌های تکراری)
        clearTimeout(searchTimeout);

        // ایجاد تایمر جدید (۳۰۰ میلی‌ثانیه صبر می‌کند تا تایپ کاربر تمام شود)
        searchTimeout = setTimeout(() => {
            let searchQuery = searchInput.value;
            let currentStatus = '{{ request('status', 'active') }}';

            // نمایش حالت لودینگ کمرنگ روی جدول (اختیاری برای زیبایی)
            tableBody.style.opacity = '0.5';

            fetch(`{{ route("employee.boxes.index") }}?status=${currentStatus}&search=${encodeURIComponent(searchQuery)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                tableBody.innerHTML = html;
                tableBody.style.opacity = '1'; // بازگرداندن وضوح جدول
            })
            .catch(error => {
                console.error('Error in search:', error);
                tableBody.style.opacity = '1';
            });

        }, 300); // 300 میلی ثانیه تاخیر
    });
</script>
@endsection
