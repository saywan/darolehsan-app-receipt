@extends('layouts.panel')

@section('content')
{{-- اضافه کردن استایل‌های تقویم شمسی --}}
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            {{-- شروع کارت فرم --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                {{-- هدر کارت --}}
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f0f0f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>صدور فیش جدید
                    </h5>
                    {{-- نمایش تاریخ امروز --}}
                    <div class="badge bg-light text-secondary p-2 fw-normal">
                        {{ function_exists('jdate') ? jdate()->format('l، d F Y') : date('Y-m-d') }}
                    </div>
                </div>

                <div class="card-body p-4">

                    {{-- نمایش خطاها --}}
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- پیام موفقیت --}}
                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('employee.receipts.store') }}" method="POST">
                        @csrf

                        {{-- ردیف ۱: اطلاعات اصلی --}}
                        <div class="row g-4 mb-4">
                            {{-- نام خیر --}}
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold text-secondary small">نام و نام خانوادگی خیر <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg bg-light border-0" name="donor_name" value="{{ old('donor_name') }}" placeholder="نام خیر را وارد کنید..." required autofocus style="height: 60px;">
                            </div>

                            {{-- تاریخ تراکنش (دارای تقویم) --}}
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-secondary small">تاریخ تراکنش <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg text-center bg-white border observer-example"
                                       name="receipt_date" id="receipt_date"
                                       value="{{ old('receipt_date', function_exists('jdate') ? jdate()->format('Y/m/d') : date('Y/m/d')) }}"
                                       required readonly style="height: 60px; border-color: #eee !important; cursor: pointer; background-color: #f8f9fa !important;">
                            </div>

                            {{-- شماره سریال (نمایشی از کنترلر) --}}
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-secondary small">شماره سریال بعدی</label>
                                <div class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-secondary fw-bold fs-5" style="height: 60px; letter-spacing: 2px;">
                                    {{ $nextSerialInfo ?? '---' }}
                                </div>
                            </div>
                        </div>

                        {{-- ردیف ۲: انتخاب‌ها --}}
                        <div class="row g-4 mb-4">
                            {{-- بخش انتخاب نوع کمک --}}
                            <div class="col-lg-8">
                                <label class="form-label fw-bold text-secondary small mb-3">انتخاب نوع کمک <span class="text-danger">*</span></label>

                                <div class="row g-2" id="helpTypeContainer">
                                    {{-- گزینه‌های ثابت --}}
                                    @php $helpTypes = ['زکات', 'صدقه',
                                    'فطریه', 'فرزاندن نیکان', 'قربانی']; @endphp

                                    @foreach($helpTypes as $index => $type)
                                    <div class="col-4 col-md-2">
                                        <input type="radio" class="btn-check help-type-radio" name="help_type" id="type_{{$index}}" value="{{ $type }}" {{ old('help_type') == $type ? 'checked' : '' }} required onchange="toggleOtherSelect(false)">
                                        <label class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-center py-3 rounded-3" for="type_{{$index}}">
                                            {{ $type }}
                                        </label>
                                    </div>
                                    @endforeach

                                    {{-- دکمه سایر --}}
                                    <div class="col-4 col-md-2">
                                        <input type="radio" class="btn-check help-type-radio" name="help_type" id="type_other" value="سایر" {{ old('help_type') == 'سایر' ? 'checked' : '' }} onchange="toggleOtherSelect(true)">
                                        <label class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-center py-3 rounded-3" for="type_other">
                                            سایر...
                                        </label>
                                    </div>
                                </div>

                                {{-- منوی کشویی گروه‌بندی شده (فقط وقتی "سایر" انتخاب شود) --}}
                                <div class="mt-3 animate__animated animate__fadeIn" id="otherTypeContainer" style="display: none;">
                                    <label class="form-label text-secondary small fw-bold">لطفاً نوع دقیق کمک را انتخاب کنید: <span class="text-danger">*</span></label>

                                    <select class="form-select form-select-lg bg-light border-0 text-secondary fw-bold" name="help_type_detail" id="helpTypeDetail">
                                        <option value="" selected disabled>-- انتخاب کنید --</option>

                                        <optgroup label="واجبات و دیون شرعی">
                                            <option value="کفاره عمد" {{ old('help_type_detail') == 'کفاره عمد' ? 'selected' : '' }}>کفاره عمد</option>
                                            <option value="کفاره غیر عمد" {{ old('help_type_detail') == 'کفاره غیر عمد' ? 'selected' : '' }}>کفاره غیر عمد</option>
                                            <option value="رد مظالم" {{ old('help_type_detail') == 'رد مظالم' ? 'selected' : '' }}>رد مظالم</option>
                                            <option value="نماز و روزه استیجاری" {{ old('help_type_detail') == 'نماز و روزه استیجاری' ? 'selected' : '' }}>نماز و روزه استیجاری</option>
                                        </optgroup>

                                        <optgroup label="حمایتی و معیشتی">
                                            <option value="کمک به ایتام" {{ old('help_type_detail') == 'کمک به ایتام' ? 'selected' : '' }}>کمک به ایتام</option>
                                            <option value="کمک هزینه درمان و دارو" {{ old('help_type_detail') == 'کمک هزینه درمان و دارو' ? 'selected' : '' }}>کمک هزینه درمان و دارو</option>
                                            <option value="کمک هزینه جهیزیه" {{ old('help_type_detail') == 'کمک هزینه جهیزیه' ? 'selected' : '' }}>کمک هزینه جهیزیه</option>
                                            <option value="کمک هزینه مسکن" {{ old('help_type_detail') == 'کمک هزینه مسکن' ? 'selected' : '' }}>کمک هزینه مسکن</option>
                                            <option value="کمک تحصیلی" {{ old('help_type_detail') == 'کمک تحصیلی' ? 'selected' : '' }}>کمک تحصیلی</option>
                                            <option value="سبد کالا" {{ old('help_type_detail') == 'سبد کالا' ? 'selected' : '' }}>سبد کالا</option>
                                            <option value="آزادی زندانیان" {{ old('help_type_detail') == 'آزادی زندانیان' ? 'selected' : '' }}>آزادی زندانیان</option>
                                        </optgroup>

                                        <optgroup label="مناسبت‌ها و نذورات">
                                            <option value="عقیقه" {{ old('help_type_detail') == 'عقیقه' ? 'selected' : '' }}>عقیقه</option>
                                            <option value="قربانی" {{ old('help_type_detail') == 'قربانی' ? 'selected' : '' }}>قربانی</option>
                                            <option value="نذورات عام" {{ old('help_type_detail') == 'نذورات عام' ? 'selected' : '' }}>نذورات عام</option>
                                            <option value="خیرات" {{ old('help_type_detail') == 'خیرات' ? 'selected' : '' }}>خیرات</option>
                                            <option value="اطعام" {{ old('help_type_detail') == 'اطعام' ? 'selected' : '' }}>اطعام</option>
                                        </optgroup>

                                        <optgroup label="عمرانی">
                                            <option value="مشارکت در ساخت" {{ old('help_type_detail') == 'مشارکت در ساخت' ? 'selected' : '' }}>مشارکت در ساخت</option>
                                            <option value="تجهیزات" {{ old('help_type_detail') == 'تجهیزات' ? 'selected' : '' }}>تجهیزات</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            {{-- روش پرداخت --}}
                            <div class="col-lg-4">
                                <label class="form-label fw-bold text-secondary small mb-3">روش پرداخت <span class="text-danger">*</span></label>
                                <div class="bg-light p-3 rounded-3 d-flex justify-content-around align-items-center" style="height: 66px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_1" value="monthly" {{ old('payment_type') == 'monthly' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold ms-1" for="pay_1">ماهانه</label>
                                    </div>
                                    <div class="vr opacity-25"></div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_2" value="occasional" {{ old('payment_type', 'occasional') == 'occasional' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold ms-1" for="pay_2">موردی</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-secondary opacity-10 my-4">

                        {{-- ردیف ۳: مبلغ و موبایل --}}
                        <div class="row g-4 mb-4">
                            {{-- مبلغ --}}
                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary small">مبلغ (ریال) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <input type="text" inputmode="numeric" class="form-control fw-bold text-primary border-primary" name="amount" id="amountInput" value="{{ old('amount') }}" placeholder="0" onkeyup="processAmount(this)" required style="height: 55px; font-size: 1.2rem; direction: ltr;">
                                    <span class="input-group-text bg-primary text-white border-primary">ریال</span>
                                </div>
                                {{-- نمایش حروف زیر فیلد --}}
                                <div class="mt-2 p-2 bg-light-subtle rounded text-primary fw-bold" id="amountTextContainer" style="display: none;">
                                    <i class="bi bi-chat-text me-1"></i>
                                    <span id="amountWords"></span> ریال
                                </div>
                                <input type="hidden" name="amount_words" id="hidden_amount_words" value="{{ old('amount_words') }}">
                            </div>

                            {{-- موبایل --}}
                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary small">شماره موبایل (اختیاری)</label>
                                <input type="tel" class="form-control form-control-lg" name="donor_mobile" value="{{ old('donor_mobile') }}" placeholder="09xxxxxxxxx" maxlength="11" style="height: 55px; direction: ltr;">
                                <div class="form-text mt-1 text-muted"><i class="bi bi-info-circle me-1"></i>در صورت وارد کردن، پیامک ارسال می‌شود.</div>
                            </div>
                        </div>

                        {{-- ردیف ۴: توضیحات --}}
                        <div class="row mb-5">
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small">توضیحات (اختیاری)</label>
                                <textarea class="form-control bg-light-subtle border-0" name="description" rows="4" placeholder="توضیحات تکمیلی، بابت، نیت خیر و ..." style="resize: none;">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        {{-- دکمه ثبت --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fs-5 fw-bold shadow-sm rounded-3">
                                <i class="bi bi-save2-fill me-2"></i> ثبت نهایی فیش
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- اسکریپت‌های مورد نیاز برای تقویم و فرم --}}
<script src="https://unpkg.com/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<script>
    // فعال‌سازی تقویم شمسی
    $(document).ready(function() {
        $('#receipt_date').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: true,
            initialValueType: 'persian', // <--- این خط مشکل تبدیل سال ۱۴۰۴ به ۷۸۳ را حل می‌کند

            autoClose: true,
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            }
        });
    });

    // ----------------------------------------------------------------
    // 1. مدیریت نمایش منوی کشویی "سایر"
    // ----------------------------------------------------------------
    function toggleOtherSelect(show) {
        const container = document.getElementById('otherTypeContainer');
        const select = document.getElementById('helpTypeDetail');

        if (show) {
            container.style.display = 'block';
            select.required = true;
        } else {
            container.style.display = 'none';
            select.value = "";
            select.required = false;
        }
    }

    // ----------------------------------------------------------------
    // 2. تبدیل اعداد فارسی/عربی به انگلیسی
    // ----------------------------------------------------------------
    function toEnglishDigits(str) {
        var persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];
        var arabicNumbers  = ["١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩", "٠"];
        var englishNumbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];

        return str.split("").map(function (char) {
            var pIndex = persianNumbers.indexOf(char);
            if (pIndex !== -1) return englishNumbers[pIndex];

            var aIndex = arabicNumbers.indexOf(char);
            if (aIndex !== -1) return englishNumbers[aIndex];

            return char;
        }).join("");
    }

    // ----------------------------------------------------------------
    // 3. پردازش مبلغ (فرمت دهی و تبدیل به حروف)
    // ----------------------------------------------------------------
    function processAmount(input) {
        let val = toEnglishDigits(input.value);
        let rawValue = val.replace(/,/g, '').replace(/\D/g, '');

        if (!rawValue) {
            input.value = '';
            document.getElementById('amountTextContainer').style.display = 'none';
            document.getElementById('hidden_amount_words').value = '';
            return;
        }

        input.value = parseInt(rawValue).toLocaleString();
        let words = Num2persian(rawValue);

        document.getElementById('amountWords').innerText = words;
        document.getElementById('amountTextContainer').style.display = 'block';
        document.getElementById('hidden_amount_words').value = words;
    }

    // ----------------------------------------------------------------
    // 4. تابع تبدیل عدد به حروف فارسی
    // ----------------------------------------------------------------
    function Num2persian(num) {
        if (parseInt(num) === 0) return "صفر";
        if (num.length > 66) return "بسیار بزرگ";

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
            let sectionStr = splitNumber[i];
            let sectionNum = parseInt(sectionStr);
            let strInWord = "";

            if (sectionNum > 0) {
                let s3 = Math.floor(sectionNum / 100);
                let s2 = sectionNum % 100;
                let s1 = sectionNum % 10;

                if (s3 > 0) {
                    strInWord = sadgan[s3];
                    if (s2 > 0) strInWord += " و ";
                }

                if (s2 > 0) {
                    if (s2 < 10) {
                        strInWord += yekan[s2];
                    } else if (s2 >= 10 && s2 < 20) {
                        strInWord += dahyek[s2 - 10];
                    } else {
                        strInWord += dahgan[Math.floor(s2 / 10)];
                        if (s1 > 0) strInWord += " و " + yekan[s1];
                    }
                }

                if (i > 0) {
                    strInWord += " " + bakhsh[i];
                }

                if (result !== "") {
                    result = strInWord + " و " + result;
                } else {
                    result = strInWord;
                }
            }
        }
        return result || "صفر";
    }

    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('type_other').checked) {
            toggleOtherSelect(true);
        }

        let amountInput = document.getElementById('amountInput');
        if(amountInput.value) {
            processAmount(amountInput);
        }
    });
</script>

<style>
    /* استایل‌های اختصاصی */
    .form-control:focus, .form-check-input:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .btn-outline-primary:hover {
        background-color: #f0f7ff;
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        transform: translateY(-2px);
    }

    optgroup {
        font-weight: bold;
        color: #6c757d;
        font-style: italic;
    }

    .animate__animated {
        animation-duration: 0.5s;
        animation-fill-mode: both;
    }
    .animate__fadeIn {
        animation-name: fadeIn;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
@endsection
