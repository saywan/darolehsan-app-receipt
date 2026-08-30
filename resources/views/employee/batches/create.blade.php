@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                {{-- هدر کارت --}}
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #f0f0f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-folder-plus me-2 text-primary"></i>تعریف دسته قبض جدید
                    </h5>
                </div>

                <div class="card-body p-4">

                    {{-- نمایش خطاها --}}
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('employee.batches.store') }}" method="POST">
                        @csrf

                        {{-- توضیح --}}
                        <div class="alert alert-info rounded-3 mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            لطفاً بازه شماره سریال‌هایی که تحویل گرفته‌اید را وارد کنید.
                        </div>

                        <div class="row g-3">
                            {{-- شماره شروع --}}
                            <div class="col-12">
                                <label for="start_number" class="form-label fw-bold text-secondary">شماره شروع (پیشنهادی)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-sort-numeric-down"></i></span>
                                    <input type="number"
                                           class="form-control form-control-lg border-start-0"
                                           id="start_number"
                                           name="start_number"
                                           value="{{ old('start_number', $suggestedStart) }}"
                                           required>
                                </div>
                                <div class="form-text">اولین شماره سریالی که در دسته چک موجود است.</div>
                            </div>

                            {{-- شماره پایان --}}
                            <div class="col-12">
                                <label for="end_number" class="form-label fw-bold text-secondary">شماره پایان</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-sort-numeric-up-alt"></i></span>
                                    <input type="number"
                                           class="form-control form-control-lg border-start-0"
                                           id="end_number"
                                           name="end_number"
                                           value="{{ old('end_number') }}"
                                           required>
                                </div>
                                <div class="form-text">آخرین شماره سریالی که در دسته چک موجود است.</div>
                            </div>

                            {{-- نمایش تعداد کل (محاسبه خودکار با جاوااسکریپت) --}}
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                    <span class="text-secondary fw-bold">تعداد کل برگ‌ها:</span>
                                    <span class="fs-5 fw-bold text-primary" id="total_count">0 عدد</span>
                                </div>
                            </div>

                            {{-- دکمه‌ها --}}
                            <div class="col-12 mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                                    <i class="bi bi-check-lg me-2"></i>ثبت دسته جدید
                                </button>
                                <a href="{{ route('employee.batches.index') }}" class="btn btn-light btn-lg w-100 fw-bold text-secondary">
                                    انصراف
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // اسکریپت ساده برای محاسبه تعداد برگ‌ها هنگام تایپ
    document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('start_number');
        const endInput = document.getElementById('end_number');
        const countDisplay = document.getElementById('total_count');

        function calculateCount() {
            const start = parseInt(startInput.value) || 0;
            const end = parseInt(endInput.value) || 0;

            if (end >= start) {
                const total = end - start + 1;
                countDisplay.textContent = new Intl.NumberFormat('fa-IR').format(total) + ' عدد';
                countDisplay.classList.remove('text-danger');
                countDisplay.classList.add('text-primary');
            } else {
                countDisplay.textContent = 'نامعتبر';
                countDisplay.classList.remove('text-primary');
                countDisplay.classList.add('text-danger');
            }
        }

        startInput.addEventListener('input', calculateCount);
        endInput.addEventListener('input', calculateCount);
    });
</script>
@endsection
