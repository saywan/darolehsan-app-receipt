@extends('layouts.panel') {{-- در صورت استفاده از layout دیگر، نام آن را بگذارید مثلاً layouts.panel --}}

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">ثبت رسید اهدای کالایی (غیرنقدی) جدید</h5>
            <a href="{{ route('employee.non-cash-receipts.index') }}" class="btn btn-sm btn-secondary">بازگشت به لیست</a>
        </div>

        <div class="card-body">
            {{-- نمایش پیام‌های خطا و موفقیت --}}
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
            @endif

            <form action="{{ route('employee.non-cash-receipts.store') }}" method="POST">
                @csrf

                {{-- ۱. مشخصات رسید و اهداکننده --}}
                <h6 class="mb-3 text-info border-bottom pb-2">۱. اطلاعات سربرگ و مشخصات اهداکننده</h6>
                <div class="row mb-4">
                    <div class="col-md-2 mb-3">
                        <label for="receipt_number">شماره رسید <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $suggestedReceiptNumber ?? '') }}" required readonly>
                        <small class="text-muted">تولید خودکار</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="receipt_date">تاریخ رسید <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-center" id="receipt_date" name="receipt_date" value="{{ old('receipt_date', $currentDate ?? '') }}" required dir="ltr">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="donor_name">نام و نام خانوادگی خیر <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="donor_name" name="donor_name" value="{{ old('donor_name') }}" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="donor_mobile">شماره موبایل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-center" id="donor_mobile" name="donor_mobile" value="{{ old('donor_mobile') }}" required dir="ltr" placeholder="09xxxxxxxxx">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="national_code">کد ملی</label>
                        <input type="text" class="form-control text-center" id="national_code" name="national_code" value="{{ old('national_code') }}" dir="ltr">
                    </div>
                </div>

                {{-- ۲. جدول اقلام اهدایی --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="text-info m-0">۲. لیست اقلام اهدایی</h6>
                    <button type="button" class="btn btn-sm btn-success shadow-sm" id="add-item-btn">
                        + افزودن ردیف کالا
                    </button>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover text-center align-middle" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">نام کالا <span class="text-danger">*</span></th>
                                <th style="width: 12%;">دسته‌بندی</th>
                                <th style="width: 10%;">مقدار <span class="text-danger">*</span></th>
                                <th style="width: 10%;">واحد <span class="text-danger">*</span></th>
                                <th style="width: 13%;">فی تقریبی (ریال)</th>
                                <th style="width: 13%;">ارزش کل (ریال)</th>
                                <th style="width: 12%;">وضعیت <span class="text-danger">*</span></th>
                                <th>توضیحات</th>
                                <th style="width: 5%;">حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm" required placeholder="مثال: برنج طارم"></td>
                                <td><input type="text" name="items[0][category]" class="form-control form-control-sm" placeholder="خواربار"></td>
                                <td><input type="number" step="any" name="items[0][quantity]" class="form-control form-control-sm quantity-input" value="1" required></td>
                                <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" value="کیلوگرم" required></td>
                                <td><input type="text" name="items[0][estimated_unit_price]" class="form-control form-control-sm price-input" placeholder="0"></td>
                                <td><input type="text" class="form-control form-control-sm total-input" readonly tabindex="-1"></td>
                                <td>
                                    <select name="items[0][condition]" class="form-control form-control-sm" required>
                                        <option value="new">نو (آکبند)</option>
                                        <option value="used_good">در حد نو / سالم</option>
                                        <option value="used_fair">کارکرده</option>
                                    </select>
                                </td>
                                <td><input type="text" name="items[0][description]" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-secondary remove-row" disabled>✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ۳. یادداشت و توضیحات --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="notes" class="text-info font-weight-bold">توضیحات کلی سند (اختیاری)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="اگر توضیح خاصی برای کل رسید وجود دارد...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="text-right border-top pt-3">
                    <button type="submit" class="btn btn-primary px-5 shadow">ثبت نهایی و صدور رسید</button>
                    <a href="{{ route('employee.non-cash-receipts.index') }}" class="btn btn-light px-4 border">انصراف</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let itemIndex = 1;

    // افزودن ردیف جدید
    document.getElementById('add-item-btn').addEventListener('click', function() {
        const tbody = document.querySelector('#items-table tbody');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td><input type="text" name="items[${itemIndex}][item_name]" class="form-control form-control-sm" required></td>
            <td><input type="text" name="items[${itemIndex}][category]" class="form-control form-control-sm"></td>
            <td><input type="number" step="any" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity-input" value="1" required></td>
            <td><input type="text" name="items[${itemIndex}][unit]" class="form-control form-control-sm" value="عدد" required></td>
            <td><input type="text" name="items[${itemIndex}][estimated_unit_price]" class="form-control form-control-sm price-input" placeholder="0"></td>
            <td><input type="text" class="form-control form-control-sm total-input" readonly tabindex="-1"></td>
            <td>
                <select name="items[${itemIndex}][condition]" class="form-control form-control-sm" required>
                    <option value="new">نو (آکبند)</option>
                    <option value="used_good">در حد نو / سالم</option>
                    <option value="used_fair">کارکرده</option>
                </select>
            </td>
            <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">✕</button></td>
        `;

        tbody.appendChild(row);
        itemIndex++;
    });

    // حذف ردیف
    document.querySelector('#items-table').addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function unformatNumber(str) {
        if(!str) return 0;
        return parseFloat(str.replace(/,/g, '')) || 0;
    }

    // محاسبه لحظه‌ای مبالغ
    document.querySelector('#items-table').addEventListener('input', function(e) {
        if(e.target.classList.contains('price-input') || e.target.classList.contains('quantity-input')) {
            const row = e.target.closest('tr');
            const qtyInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const totalInput = row.querySelector('.total-input');

            if(e.target.classList.contains('price-input')) {
                let val = e.target.value.replace(/,/g, '');
                if(!isNaN(val) && val !== '') {
                    e.target.value = formatNumber(val);
                }
            }

            const qty = parseFloat(qtyInput.value) || 0;
            const price = unformatNumber(priceInput.value);

            if (qty > 0 && price > 0) {
                totalInput.value = formatNumber(qty * price);
            } else {
                totalInput.value = '';
            }
        }
    });
});
</script>
@endsection
