@extends('layouts.auth')

@section('content')
    <div class="auth-header">
        <div class="auth-icon">
            <i class="bi bi-box-arrow-in-right"></i>
        </div>
        <h4>ورود به حساب کاربری</h4>
        <p>موسسه خیریه دارالاحسان</p>
    </div>

    <div class="auth-body">

        <!-- نمایش خطاها -->
        @if ($errors->any())
            <div class="alert alert-danger p-2 small mb-3 text-center border-0 bg-danger bg-opacity-10 text-danger rounded-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <!-- ورودی موبایل -->
            <div class="custom-input-group">
                <input type="text" name="mobile" placeholder="نام کاربری (شماره موبایل)" value="{{ old('mobile') }}" required autofocus dir="rtl">
                <i class="bi bi-phone"></i>
            </div>

            <!-- ورودی رمز عبور -->
            <div class="custom-input-group">
                <input type="password" name="password" placeholder="رمز عبور" required dir="rtl">
                <i class="bi bi-lock"></i>
            </div>

            <!-- دکمه ورود -->
            <button type="submit" class="btn-auth">
                ورود به سیستم
                <i class="bi bi-arrow-left short me-1"></i>
            </button>
        </form>

        <div class="auth-footer">
            هنوز ثبت نام نکرده‌اید؟ <a href="{{ route('register') }}">ایجاد حساب جدید</a>
        </div>
    </div>
@endsection
