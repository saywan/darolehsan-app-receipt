@extends('layouts.panel')

@section('title', 'تخلیه صندوق و ثبت مبلغ')

@section('content')
<div class="container-fluid mt-4 mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-box-open"></i> ثبت مبلغ و تخلیه صندوق</h5>
        </div>
        <div class="card-body">

            {{-- نمایش اطلاعات صندوق به کاربر برای اطمینان --}}
            <div class="alert alert-info mb-4" style="background-color: #d1f4fa; border: 1px solid #b2e8f5; border-radius: 8px;">
                <strong class="text-dark"><i class="fas fa-info-circle"></i> مشخصات صندوق تخصیص داده شده:</strong>
                <ul class="mb-0 mt-3 text-dark lh-lg list-unstyled">
                    <li><i class="fas fa-hashtag text-primary ms-2"></i> <strong>کد صندوق:</strong> {{ $boxAllocation->charityBox->code ?? 'نامشخص' }}</li>
                    <li><i class="fas fa-box text-primary ms-2"></i> <strong>نوع:</strong>
                        @if(isset($boxAllocation->charityBox) && $boxAllocation->charityBox->type == 'plastic')
                            پلاستیکی <small class="text-danger fw-bold">(یکبار مصرف - پس از ثبت باطل می‌شود)</small>
                        @else
                            شیشه‌ای <small class="text-success fw-bold">(قابل شارژ - پس از تخلیه موجود می‌شود)</small>
                        @endif
                    </li>
                    <li><i class="fas fa-user text-primary ms-2"></i> <strong>نام نیکوکار:</strong> {{ $boxAllocation->receiver_name ?? $boxAllocation->applicant_name }}</li>
                    <li><i class="fas fa-phone text-primary ms-2"></i> <strong>شماره موبایل:</strong> {{ $boxAllocation->receiver_phone ?? $boxAllocation->applicant_mobile }}</li>
                </ul>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- فرم ثبت مبلغ --}}
            <form action="{{ route('employee.boxes.update', $boxAllocation->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="fw-bold mb-3" style="font-size: 1.1rem;">مبلغ جمع‌آوری شده (ریال) <span class="text-danger">*</span></label>

                        <!-- فیلد نمایشی برای کاربر (فرمت شده با کاما) -->
                        <input type="text"
                               id="amount_view"
                               class="form-control form-control-lg text-center shadow-sm"
                               placeholder="مثلاً: 500,000"
                               inputmode="numeric"
                               style="font-size: 1.8rem; letter-spacing: 2px; font-weight: bold; border: 2px solid #ccc; border-radius: 10px;"
                               required
                               autofocus>

                        <!-- فیلد مخفی برای ارسال به دیتابیس (بدون کاما) -->
                        <input type="hidden" name="amount" id="amount_actual" required>

                        <!-- نمایش مبلغ به حروف -->
                        <div id="amount_words" class="text-success fw-bold text-center mt-3" style="font-size: 1.2rem; min-height: 30px;"></div>

                        <small class="text-muted d-block mt-3 text-center"><i class="fas fa-exclamation-triangle text-warning"></i> لطفاً مبلغ را به دقت و به صورت عدد وارد کنید.</small>
                    </div>
                </div>

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg shadow-sm px-4">
                        <i class="fas fa-check-circle"></i> ثبت مبلغ و اتمام
                    </button>
                    <a href="{{ route('employee.boxes.index') }}" class="btn btn-secondary btn-lg shadow-sm px-4">انصراف</a>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- کدهای جاوا اسکریپت در انتهای همین بخش برای اطمینان از اجرا شدن -->
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // تابع تبدیل اعداد فارسی/عربی به انگلیسی
        function toEnglishDigits(str) {
            const persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g];
            const arabicNumbers  = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g];
            if (typeof str === 'string') {
                for (let i = 0; i < 10; i++) {
                    str = str.replace(persianNumbers[i], i).replace(arabicNumbers[i], i);
                }
            }
            return str;
        }

        // کتابخانه کوچک تبدیل عدد به حروف فارسی
        const Num2persian = (function () {
            const yekan = ["", "یک", "دو", "سه", "چهار", "پنج", "شش", "هفت", "هشت", "نه"];
            const dahgan = ["", "ده", "بیست", "سی", "چهل", "پنجاه", "شصت", "هفتاد", "هشتاد", "نود"];
            const dahha = ["ده", "یازده", "دوازده", "سیزده", "چهارده", "پانزده", "شانزده", "هفده", "هجده", "نوزده"];
            const sadgan = ["", "صد", "دویست", "سیصد", "چهارصد", "پانصد", "ششصد", "هفتصد", "هشتصد", "نهصد"];
            const bases = ["", "هزار", "میلیون", "میلیارد", "تریلیون", "کوادریلیون"];

            function convert(num) {
                if (num === 0) return "صفر";
                let str = num.toString();
                if (str.length === 0) return "";
                if (str.length > 18) return "عدد بسیار بزرگ است";

                let parts = [];
                while (str.length > 0) {
                    parts.push(str.substring(Math.max(0, str.length - 3)));
                    str = str.substring(0, Math.max(0, str.length - 3));
                }

                let words = [];
                for (let i = 0; i < parts.length; i++) {
                    let partNum = parseInt(parts[i], 10);
                    if (partNum !== 0) {
                        let partWord = getThreeDigitWord(parts[i]);
                        if (i > 0) partWord += " " + bases[i];
                        words.push(partWord);
                    }
                }
                return words.reverse().join(" و ");
            }

            function getThreeDigitWord(numStr) {
                let num = parseInt(numStr, 10);
                if (num === 0) return "";
                let s = Math.floor(num / 100);
                let d = Math.floor((num % 100) / 10);
                let y = num % 10;

                let result = [];
                if (s > 0) result.push(sadgan[s]);

                if (d === 1 && y >= 0) {
                    result.push(dahha[y]);
                } else {
                    if (d > 1) result.push(dahgan[d]);
                    if (y > 0) result.push(yekan[y]);
                }
                return result.join(" و ");
            }

            return convert;
        })();

        const amountView = document.getElementById('amount_view');
        const amountActual = document.getElementById('amount_actual');
        const amountWords = document.getElementById('amount_words');

        if(amountView) {
            amountView.addEventListener('input', function (e) {
                // ۱. تبدیل حروف فارسی کیبورد موبایل به انگلیسی
                let value = toEnglishDigits(e.target.value);

                // ۲. حذف هر کاراکتری غیر از عدد (حذف کاماها و حروف)
                let cleanNumber = value.replace(/\D/g, "");

                if (cleanNumber.length > 0) {
                    // ۳. آپدیت فیلد مخفی برای ارسال به سرور
                    amountActual.value = cleanNumber;

                    // ۴. اضافه کردن کاما برای نمایش به کاربر
                    e.target.value = Number(cleanNumber).toLocaleString('en-US');

                    // ۵. تبدیل عدد به حروف و نمایش کلمه "ریال"
                    amountWords.innerText = Num2persian(parseInt(cleanNumber)) + " ریال";
                } else {
                    amountActual.value = "";
                    e.target.value = "";
                    amountWords.innerText = "";
                }
            });
        }
    });
</script>
@endsection
