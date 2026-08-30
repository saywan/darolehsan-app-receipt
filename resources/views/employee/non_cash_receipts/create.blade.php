@extends('layouts.panel')

@section('title', 'ثبت رسید غیرنقدی جدید')
@section('header_title', 'ثبت رسید غیرنقدی')

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <strong class="fw-bold">لطفاً خطاهای زیر را بررسی و برطرف کنید:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bi bi-box-seam me-2 text-primary"></i>فرم ثبت کمک غیرنقدی
            </h5>
            <a href="{{ route('employee.non-cash-receipts.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-arrow-right me-1"></i>بازگشت به لیست
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('employee.non-cash-receipts.store') }}" method="POST" id="receiptForm">
                @csrf

                {{-- بخش ۱: اطلاعات خیر --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">
                            <i class="bi bi-person-badge me-1"></i>مشخصات خیر / اهداکننده
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <label for="donor_name" class="form-label fw-semibold">نام و نام خانوادگی خیر <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('donor_name') is-invalid @enderror" id="donor_name" name="donor_name" value="{{ old('donor_name') }}" placeholder="مثال: عبداله جوانمیری" required>
                    </div>

                    <div class="col-md-4">
                        <label for="donor_mobile" class="form-label fw-semibold">شماره موبایل خیر <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 text-start @error('donor_mobile') is-invalid @enderror" id="donor_mobile" name="donor_mobile" value="{{ old('donor_mobile') }}" placeholder="09181597577" dir="ltr" required>
                    </div>

                    <div class="col-md-4">
                        <label for="receipt_date" class="form-label fw-semibold">تاریخ دریافت <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 text-start @error('receipt_date') is-invalid @enderror" id="receipt_date" name="receipt_date" value="{{ old('receipt_date', verta()->format('Y/m/d')) }}" dir="ltr" required>
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label fw-semibold">توضیحات و یادداشت (اختیاری)</label>
                        <textarea class="form-control rounded-3" id="notes" name="notes" rows="2" placeholder="توضیحات تکمیلی...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- بخش ۲: اقلام اهدایی --}}
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-muted mb-0">
                            <i class="bi bi-card-checklist me-1"></i>اقلام اهدایی
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-3" id="addItemBtn">
                            <i class="bi bi-plus-circle me-1"></i>افزودن قلم کالا
                        </button>
                    </div>

                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width: 25%;">عنوان کالا / خدمات <span class="text-danger">*</span></th>
                                        <th style="width: 12%;">تعداد / مقدار <span class="text-danger">*</span></th>
                                        <th style="width: 13%;">واحد سنجش <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">وضعیت کالا</th>
                                        <th style="width: 15%;">ارزش تخمینی واحد (ریال)</th>
                                        <th style="width: 15%;">ارزش کل (ریال)</th>
                                        <th style="width: 5%;">حذف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <input type="text" name="items[0][item_name]" class="form-control rounded-3" placeholder="مثال: برنج ایرانی" required>
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="items[0][quantity]" class="form-control rounded-3 item-qty text-center" placeholder="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][unit]" class="form-control rounded-3 text-center" placeholder="عدد" value="عدد" required>
                                        </td>
                                        <td>
                                            <select name="items[0][condition]" class="form-select rounded-3">
                                                <option value="new" selected>نو / آکبند</option>
                                                <option value="used_good">در حد نو</option>
                                                <option value="used_fair">دست دوم (کارکرده)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][estimated_unit_price]" class="form-control rounded-3 item-price text-start" placeholder="0" dir="ltr">
                                        </td>
                                        <td class="text-center fw-bold item-total-display">0</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn rounded-3" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end fw-bold">جمع کل ارزش تخمینی:</th>
                                        <th colspan="2" class="fw-bold text-success" id="grandTotalDisplay">0 ریال</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('employee.non-cash-receipts.index') }}" class="btn btn-light rounded-3 px-4">انصراف</a>
                    <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>ثبت و ذخیره رسید
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 1;
    const itemsTableBody = document.querySelector('#itemsTable tbody');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function unformatNumber(str) {
        return parseFloat((str || '').toString().replace(/,/g, '')) || 0;
    }

    function calculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = unformatNumber(row.querySelector('.item-price').value);
            const total = qty * price;
            row.querySelector('.item-total-display').textContent = formatNumber(total);
            grandTotal += total;
        });
        grandTotalDisplay.textContent = formatNumber(grandTotal) + ' ریال';
    }

    function attachRowEvents(row) {
        const priceInput = row.querySelector('.item-price');
        const qtyInput = row.querySelector('.item-qty');
        const removeBtn = row.querySelector('.remove-item-btn');

        priceInput.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            this.value = val ? formatNumber(val) : '';
            calculateTotals();
        });

        qtyInput.addEventListener('input', calculateTotals);

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (document.querySelectorAll('#itemsTable tbody tr').length > 1) {
                    row.remove();
                    calculateTotals();
                    updateRemoveButtons();
                }
            });
        }
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('#itemsTable tbody tr');
        rows.forEach(r => {
            const btn = r.querySelector('.remove-item-btn');
            btn.disabled = (rows.length === 1);
        });
    }

    document.getElementById('addItemBtn').addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][item_name]" class="form-control rounded-3" placeholder="عنوان کالا" required>
            </td>
            <td>
                <input type="number" step="any" name="items[${rowIndex}][quantity]" class="form-control rounded-3 item-qty text-center" placeholder="1" value="1" required>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][unit]" class="form-control rounded-3 text-center" placeholder="عدد" value="عدد" required>
            </td>
            <td>
                <select name="items[${rowIndex}][condition]" class="form-select rounded-3">
                    <option value="new" selected>نو / آکبند</option>
                    <option value="used_good">در حد نو</option>
                    <option value="used_fair">دست دوم (کارکرده)</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][estimated_unit_price]" class="form-control rounded-3 item-price text-start" placeholder="0" dir="ltr">
            </td>
            <td class="text-center fw-bold item-total-display">0</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn rounded-3">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        itemsTableBody.appendChild(newRow);
        attachRowEvents(newRow);
        rowIndex++;
        updateRemoveButtons();
    });

    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        attachRowEvents(row);
    });

    document.getElementById('receiptForm').addEventListener('submit', function() {
        document.querySelectorAll('.item-price').forEach(input => {
            input.value = input.value.replace(/,/g, '');
        });
    });
});
</script>
@endsection
