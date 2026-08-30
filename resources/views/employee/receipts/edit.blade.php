@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>ویرایش فیش صادره
                    </h5>
                    <span class="badge bg-warning text-dark">سریال: {{ $receipt->serial_number }}</span>
                </div>

                <div class="card-body p-4">

                    {{-- نمایش خطاها --}}
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('employee.receipts.update', $receipt->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- ردیف ۱: اطلاعات اصلی --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">نام خیر <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg bg-light border-0" name="donor_name" value="{{ old('donor_name', $receipt->donor_name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">شماره موبایل</label>
                                <input type="tel" class="form-control form-control-lg" name="donor_mobile" value="{{ old('donor_mobile', $receipt->donor_mobile) }}" placeholder="09xxxxxxxxx" maxlength="11" style="direction: ltr;">
                            </div>
                        </div>

                        {{-- ردیف ۲: نوع کمک (منطق هوشمند برای انتخاب) --}}
                        @php
                            $mainTypes = ['زکات', 'صدقه', 'فطریه', 'نیکان', 'عید قربان'];
                            // آیا نوع کمک فعلی جزو لیست اصلی است؟
                            $isMainType = in_array($receipt->help_type, $mainTypes);
                            // مقدار پیش فرض برای "سایر"
                            $otherValue = $isMainType ? '' : $receipt->help_type;
                        @endphp

                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small mb-3">انتخاب نوع کمک <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    {{-- گزینه‌های اصلی --}}
                                    @foreach($mainTypes as $index => $type)
                                    <div class="col-6 col-md-2">
                                        <input type="radio" class="btn-check help-type-radio" name="help_type" id="type_{{$index}}" value="{{ $type }}"
                                            {{ (old('help_type', $receipt->help_type) == $type) ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-primary w-100 py-2 rounded-3" for="type_{{$index}}">
                                            {{ $type }}
                                        </label>
                                    </div>
                                    @endforeach

                                    {{-- گزینه سایر --}}
                                    <div class="col-6 col-md-2">
                                        <input type="radio" class="btn-check help-type-radio" name="help_type" id="type_other" value="سایر"
                                            {{ (! $isMainType || old('help_type') == 'سایر') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary w-100 py-2 rounded-3" for="type_other">
                                            سایر...
                                        </label>
                                    </div>
                                </div>

                                {{-- لیست کشویی برای سایر (مخفی/نمایان) --}}
                                <div class="mt-3 collapse {{ (! $isMainType || old('help_type') == 'سایر') ? 'show' : '' }}" id="otherTypeCollapse">
                                    <label class="form-label small text-muted">لطفاً جزئیات نوع کمک را انتخاب کنید:</label>
                                    <select class="form-select bg-light" name="help_type_detail" id="helpTypeDetail">
                                        <option value="" disabled selected>انتخاب کنید...</option>
                                        <optgroup label="نذورات و کفارات">
                                            <option value="کفاره" {{ (old('help_type_detail', $otherValue) == 'کفاره') ? 'selected' : '' }}>کفاره</option>
                                            <option value="رد مظالم" {{ (old('help_type_detail', $otherValue) == 'رد مظالم') ? 'selected' : '' }}>رد مظالم</option>
                                            <option value="عقیقه" {{ (old('help_type_detail', $otherValue) == 'عقیقه') ? 'selected' : '' }}>عقیقه</option>
                                            <option value="نیابتی" {{ (old('help_type_detail', $otherValue) == 'نیابتی') ? 'selected' : '' }}>نیابتی</option>
                                        </optgroup>
                                        <optgroup label="حمایت‌های خاص">
                                            <option value="ایتام" {{ (old('help_type_detail', $otherValue) == 'ایتام') ? 'selected' : '' }}>ایتام</option>
                                            <option value="جهیزیه" {{ (old('help_type_detail', $otherValue) == 'جهیزیه') ? 'selected' : '' }}>جهیزیه</option>
                                            <option value="درمان" {{ (old('help_type_detail', $otherValue) == 'درمان') ? 'selected' : '' }}>درمان</option>
                                            <option value="مسکن" {{ (old('help_type_detail', $otherValue) == 'مسکن') ? 'selected' : '' }}>مسکن</option>
                                            <option value="تحصیلی" {{ (old('help_type_detail', $otherValue) == 'تحصیلی') ? 'selected' : '' }}>تحصیلی</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ردیف ۳: مبلغ و روش پرداخت --}}
                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary small">مبلغ (ریال) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    {{-- مقدار دیتابیس (که ریال است) مستقیماً نمایش داده می‌شود بدون تقسیم بر ۱۰ --}}
                                    <input type="text" class="form-control fw-bold text-primary" name="amount" id="amountInput"
                                           value="{{ old('amount', $receipt->amount_rials) }}"
                                           onkeyup="processAmount(this)" required style="direction: ltr;">
                                    <span class="input-group-text bg-light">ریال</span>
                                </div>
                                <div class="mt-2 text-primary fw-bold small" id="amountWords"></div>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary small">روش پرداخت</label>
                                <div class="bg-light p-3 rounded-3 d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_1" value="monthly" {{ old('payment_type', $receipt->payment_type) == 'monthly' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pay_1">ماهانه</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_2" value="occasional" {{ old('payment_type', $receipt->payment_type) == 'occasional' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pay_2">موردی</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">توضیحات</label>
                            <textarea class="form-control bg-light border-0" name="description" rows="3">{{ old('description', $receipt->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                           <a href="{{ route('employee.batches.receipts', $receipt->receipt_batch_id) }}" class="btn btn-light border">بازگشت</a>
                           <button type="submit" class="btn btn-warning px-5 fw-bold">ذخیره تغییرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // مدیریت نمایش/مخفی کردن لیست "سایر"
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('.help-type-radio');
        const collapseElement = document.getElementById('otherTypeCollapse');
        const detailSelect = document.getElementById('helpTypeDetail');

        // اجرای اولیه برای فرمت مبلغ هنگام لود شدن صفحه برای نمایش کاما و حروف
        processAmount(document.getElementById('amountInput'));

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'سایر') {
                    collapseElement.classList.add('show');
                    detailSelect.setAttribute('required', 'required');
                } else {
                    collapseElement.classList.remove('show');
                    detailSelect.removeAttribute('required');
                    detailSelect.value = ""; 
                }
            });
        });
    });

    // توابع تبدیل اعداد به انگلیسی و جداکننده هزارگان
    function toEnglishDigits(str) {
        var persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];
        var englishNumbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
        return str.toString().split("").map(function (char) {
            var index = persianNumbers.indexOf(char);
            return index !== -1 ? englishNumbers[index] : char;
        }).join("");
    }

    function processAmount(input) {
        let val = input.value;
        val = toEnglishDigits(val);
        let rawValue = val.replace(/,/g, '').replace(/\D/g, '');

        if (!rawValue) {
            input.value = '';
            document.getElementById('amountWords').innerText = '';
            return;
        }

        input.value = parseInt(rawValue).toLocaleString();
        // اضافه کردن کلمه ریال به انتهای حروف
        document.getElementById('amountWords').innerText = Num2persian(rawValue) + " ریال";
    }

    // تابع کامل تبدیل عدد به حروف فارسی
    function Num2persian(num) {
        if (parseInt(num) === 0) return "صفر";
        
        const yekan = ["", "یک", "دو", "سه", "چهار", "پنج", "شش", "هفت", "هشت", "نه"];
        const dahgan = ["", "", "بیست", "سی", "چهل", "پنجاه", "شصت", "هفتاد", "هشتاد", "نود"];
        const dahyek = ["ده", "یازده", "دوازده", "سیزده", "چهارده", "پانزده", "شانزده", "هفده", "هجده", "نوزده"];
        const sadgan = ["", "یکصد", "دویست", "سیصد", "چهارصد", "پانصد", "ششصد", "هفتصد", "هشتصد", "نهصد"];
        const bakhsh = ["", "هزار", "میلیون", "میلیارد", "تریلیون"];

        let splitNumber = [];
        let str = num.toString();
        while (str.length > 3) {
            splitNumber.push(str.substring(str.length - 3));
            str = str.substring(0, str.length - 3);
        }
        splitNumber.push(str);

        let result = "";
        for (let i = 0; i < splitNumber.length; i++) {
            let sectionNum = parseInt(splitNumber[i]);
            if (sectionNum > 0) {
                let strInWord = "";
                let s3 = Math.floor(sectionNum / 100);
                let s2 = sectionNum % 100;
                let s1 = sectionNum % 10;

                if (s3 > 0) { strInWord = sadgan[s3]; if (s2 > 0) strInWord += " و "; }
                if (s2 > 0) {
                    if (s2 < 10) strInWord += yekan[s2];
                    else if (s2 < 20) strInWord += dahyek[s2 - 10];
                    else { strInWord += dahgan[Math.floor(s2 / 10)]; if (s1 > 0) strInWord += " و " + yekan[s1]; }
                }
                if (i > 0) strInWord += " " + bakhsh[i];
                result = (result !== "") ? strInWord + " و " + result : strInWord;
            }
        }
        return result;
    }
</script>
@endsection
