@extends('layouts.app')

@section('content')
<!-- نوار بالای صفحه -->
<div class="glass-nav d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <span class="fw-bold fs-5 ms-3">دارالاحسان</span>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary me-2">لیست رسیدها</a>
        <a href="{{ route('receipt.create') }}" class="btn btn-sm btn-primary">صدور رسید جدید</a>
    </div>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="btn btn-sm btn-danger rounded-pill">خروج</button>
    </form>
</div>

<div class="glass-card">
    <div class="row mb-4 border-bottom pb-2">
        <div class="col-md-6">
            <h5 class="mb-0 text-primary">فرم ثبت کمک نقدی</h5>
            <small class="text-muted">لطفا اطلاعات خیر را با دقت وارد نمایید</small>
        </div>
        <div class="col-md-6 text-end">
            <span class="badge bg-info text-dark p-2">سری: ۰۲۰۸</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3 mb-3">{{ session('success') }}</div>
    @endif

    <form action="{{ route('receipt.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <!-- ردیف اول: کدها و تاریخ -->
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="doc_code" placeholder="کد مدرک" value="06FM...">
                    <label>کد مدرک</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="date" class="form-control" name="receipt_date" required>
                    <label>تاریخ</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="serial_number" value="{{ $nextSerial }}" readonly style="background-color: rgba(0,0,0,0.05)">
                    <label>شماره رسید (سریال)</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="donor_mobile" placeholder="شماره">
                    <label>شماره همراه خیر</label>
                </div>
            </div>

            <!-- ردیف دوم: مشخصات فردی -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" name="donor_name" placeholder="نام" required>
                    <label>جناب آقای / سرکار خانم</label>
                </div>
            </div>
            <div class="col-md-6">
                 <!-- بخش نوع کمک -->
                <div class="d-flex align-items-center h-100 ps-2 bg-light rounded-3" style="background: rgba(255,255,255,0.5) !important; border: 1px solid rgba(0,0,0,0.1);">
                    <span class="me-3 text-muted">نوع پرداخت:</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" value="occasional" id="type1" checked>
                        <label class="form-check-label" for="type1">موردی</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" value="monthly" id="type2">
                        <label class="form-check-label" for="type2">ماهیانه</label>
                    </div>
                </div>
            </div>

            <!-- ردیف سوم: مبالغ -->
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="number" class="form-control" name="amount_rials" id="amountRials" placeholder="ریال" required>
                    <label>مبلغ به ریال</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" name="amount_words" id="amountWords" placeholder="حروف">
                    <label>مبلغ به حروف</label>
                </div>
            </div>

            <!-- ردیف چهارم: صندوق و توضیحات -->
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="box_code_old" placeholder="قدیم">
                    <label>کد صندوق قدیم</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="box_code_new" placeholder="جدید">
                    <label>کد صندوق جدید</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" name="description" placeholder="بابت...">
                    <label>بابت کمک به (فرزندان و...)</label>
                </div>
            </div>

            <!-- دکمه ثبت -->
            <div class="col-12 mt-4 text-center">
                <button type="submit" class="btn btn-primary-glass w-50 py-3 fs-5">
                    ثبت رسید و صدور
                </button>
            </div>
        </div>
    </form>

    <div class="mt-4 text-center text-muted border-top pt-3">
        <small class="d-block">آدرس: سنندج، چهارراه شهدا، پاساژ عزتی، طبقه اول | تلفن: ۳۳۱۶۷۲۶۶</small>
        <small class="d-block">www.darolehsan.com</small>
    </div>
</div>

<script>
    // اسکریپت ساده برای تبدیل عدد به حروف (نیاز به کتابخانه کامل‌تر دارد، اینجا نمایشی است)
    document.getElementById('amountRials').addEventListener('input', function(e) {
        // اینجا می‌توانید از یک تابع جاوااسکریپت برای تبدیل عدد به حروف فارسی استفاده کنید
        // فعلا برای دمو خالی است
    });
</script>
@endsection
