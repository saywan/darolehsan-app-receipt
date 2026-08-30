

@extends('layouts.panel')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">ثبت و تحویل صندوق جدید</h5>
        </div>
        <div class="card-body">

            <!-- بخش نمایش خطاهای فرم (بسیار مهم برای فهمیدن علت ثبت نشدن) -->
           <!-- نمایش پیام موفقیت -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- نمایش پیام خطای سیستمی (مثل خطای دیتابیس) -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- نمایش خطاهای فرم (مثل اشتباه بودن شماره موبایل) -->
    @if ($errors->any())
        <div class="alert alert-warning">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

            <form action="{{ route('employee.boxes.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- نوع صندوق -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">نوع صندوق <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="plastic" {{ old('type') == 'plastic' ? 'selected' : '' }}>پلاستیکی (یکبار مصرف/قلک)</option>
                            <option value="glass" {{ old('type') == 'glass' ? 'selected' : '' }}>شیشه‌ای/فلزی (دائمی)</option>
                        </select>
                        <small class="text-muted">پلاستیکی پس از تخلیه باطل می‌شود، شیشه‌ای قابل استفاده مجدد است.</small>
                    </div>

                    <!-- کد صندوق -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">کد صندوق / هولوگرام <span class="text-danger">*</span></label>
                        <!-- تغییر نام از box_code به code -->
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="مثلاً: 10052" required>
                    </div>

                    <!-- نام مشترک -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">نام و نام خانوادگی تحویل گیرنده <span class="text-danger">*</span></label>
                        <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" class="form-control" required>
                    </div>

                    <!-- کدملی -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">کد ملی</label>
                        <input type="text" name="applicant_national_code" value="{{ old('applicant_national_code') }}" class="form-control">
                    </div>

                    <!-- موبایل -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">شماره همراه <span class="text-danger">*</span></label>
                        <input type="text" name="applicant_mobile" value="{{ old('applicant_mobile') }}" class="form-control" maxlength="11" placeholder="09..." required>
                    </div>

                    <!-- آدرس -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">آدرس دقیق</label>
                        <textarea name="applicant_address" class="form-control" rows="2">{{ old('applicant_address') }}</textarea>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> ثبت و تحویل صندوق
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
