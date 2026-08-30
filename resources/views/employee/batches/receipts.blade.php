@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            {{-- کارت اصلی --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                {{-- هدر کارت --}}
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f0f0f0; border-radius: 15px 15px 0 0;">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">
                            <i class="bi bi-receipt-cutoff me-2 text-primary"></i>
                            ریز فیش‌های دسته {{ $batch->id }}
                        </h5>
                        <small class="text-muted">
                            بازه سریال: {{ number_format($batch->start_number) }} تا {{ number_format($batch->end_number) }}
                        </small>
                    </div>

                    <a href="{{ route('employee.batches.index') }}" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="bi bi-arrow-right me-1"></i> بازگشت به لیست دسته‌ها
                    </a>
                </div>

                <div class="card-body">

                    {{-- پیام‌های موفقیت --}}
                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- فرم جستجو --}}
                    <form action="{{ route('employee.batches.receipts', $batch->id) }}" method="GET" class="mb-4">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0 py-2" 
                                   placeholder="جستجو بر اساس نام خیر، شماره سریال یا مبلغ..." 
                                   value="{{ request('search') }}">
                            <button class="btn btn-primary px-4 fw-bold" type="submit">جستجو</button>
                            @if(request('search'))
                                <a href="{{ route('employee.batches.receipts', $batch->id) }}" class="btn btn-light border px-3" title="پاک کردن فیلتر">
                                    <i class="bi bi-x-lg text-danger"></i>
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- جدول داده‌ها --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" class="py-3 text-secondary text-center" style="width: 100px;">سریال</th>
                                    <th scope="col" class="py-3 text-secondary">نام خیر</th>
                                    <th scope="col" class="py-3 text-secondary">مبلغ (تومان)</th>
                                    <th scope="col" class="py-3 text-secondary">نوع کمک</th>
                                    <th scope="col" class="py-3 text-secondary">تاریخ</th>
                                    <th scope="col" class="py-3 text-secondary text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receipts as $receipt)
                                    <tr>
                                        {{-- سریال --}}
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-dark font-monospace fs-6">
                                                {{ $receipt->serial_number }}
                                            </span>
                                        </td>

                                        {{-- نام خیر --}}
                                        <td class="fw-bold text-dark">
                                            {{ $receipt->donor_name }}
                                            @if($receipt->donor_mobile)
                                                <small class="d-block text-muted fw-normal font-monospace mt-1">
                                                    {{ $receipt->donor_mobile }}
                                                </small>
                                            @endif
                                        </td>

                                        {{-- مبلغ --}}
                                        <td class="text-primary fw-bold">
                                            {{ number_format($receipt->amount_rials / 10) }}
                                        </td>

                                        {{-- نوع کمک --}}
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                                {{ $receipt->help_type }}
                                            </span>
                                            <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">
                                                {{ $receipt->payment_type == 'monthly' ? 'ماهانه' : 'موردی' }}
                                            </small>
                                        </td>

                                        {{-- تاریخ --}}
                                        <td class="text-muted small">
                                            @if(function_exists('jdate'))
                                                {{ jdate($receipt->receipt_date)->format('Y/m/d') }}<br>
                                                {{ jdate($receipt->receipt_date)->format('H:i') }}
                                            @else
                                                {{ $receipt->receipt_date->format('Y-m-d') }}
                                            @endif
                                        </td>

                                        {{-- عملیات --}}
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('employee.receipts.edit', $receipt->id) }}" 
                                                   class="btn btn-light text-primary border shadow-sm btn-icon" 
                                                   title="ویرایش">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                
                                                {{-- <button type="button" 
                                                        class="btn btn-light text-danger border shadow-sm btn-icon" 
                                                        title="ابطال فیش"
                                                        onclick="confirmDelete('{{ route('employee.receipts.destroy', $receipt->id) }}', '{{ $receipt->serial_number }}', '{{ number_format($receipt->amount_rials / 10) }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button> --}}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                                <i class="bi bi-receipt-cutoff fs-1 mb-2"></i>
                                                <p class="mb-0">هنوز هیچ فیشی در این دسته ثبت نشده است.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- صفحه‌بندی --}}
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $receipts->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- مودال تایید حذف --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4">
                <div class="mb-3 text-danger bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 70px; height: 70px;">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark mb-3">آیا از ابطال این فیش اطمینان دارید؟</h5>
                <p class="text-muted mb-4 px-3">
                    شما در حال حذف فیش با شماره سریال <span id="modalSerial" class="fw-bold text-dark fs-5">---</span> 
                    به مبلغ <span id="modalAmount" class="fw-bold text-dark fs-5">---</span> تومان هستید.
                    <br>
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark text-start mx-3 mt-3">
                        <small class="d-flex">
                            <i class="bi bi-info-circle-fill me-2 mt-1 text-warning"></i>
                            <div>
                                <strong>توجه:</strong> عملیات ابطال غیرقابل بازگشت است.<br>
                                در صورتی که این <u>آخرین فیش صادر شده</u> باشد، شماره سریال دسته قبض به عقب باز می‌گردد.
                            </div>
                        </small>
                    </div>
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light py-2 px-4 fw-bold rounded-3" data-bs-dismiss="modal">انصراف</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger py-2 px-4 fw-bold rounded-3 shadow-sm">
                            <i class="bi bi-trash-fill me-2"></i>بله، ابطال شود
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-icon { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
    .btn-icon:hover { background-color: #e2e6ea; transform: translateY(-2px); }
    .font-monospace { letter-spacing: 1px; }
</style>

{{-- اسکریپت جاوااسکریپت (اصلاح شده) --}}
<script>
    function confirmDelete(url, serial, amount) {
        var form = document.getElementById('deleteForm');
        var modalSerial = document.getElementById('modalSerial');
        var modalAmount = document.getElementById('modalAmount');
        var modalEl = document.getElementById('deleteModal');

        if(form) form.action = url;
        if(modalSerial) modalSerial.innerText = serial;
        if(modalAmount) modalAmount.innerText = amount;

        if(modalEl) {
            var myModal = new bootstrap.Modal(modalEl);
            myModal.show();
        }
    }
</script>
@endsection
