@extends('layouts.auth')

@section('content')
    <div class="auth-header">
        <div class="auth-icon">
            <i class="bi bi-person-plus"></i>
        </div>
        <h4>ایجاد حساب کاربری</h4>
        <p>عضویت در خانواده دارالاحسان</p>
    </div>

    <div class="auth-body">
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
        <form action="{{ route('register') }}" method="POST">
            @csrf

            <!-- نام و نام خانوادگی -->
            <div class="custom-input-group">
                <input type="text" name="name" placeholder="نام و نام خانوادگی" value="{{ old('name') }}" required>
                <i class="bi bi-person"></i>
            </div>

            <!-- موبایل -->
            <div class="custom-input-group">
                <input type="text" name="mobile" placeholder="شماره موبایل (مثال: 0912...)" value="{{ old('mobile') }}" required>
                <i class="bi bi-phone"></i>
            </div>

            <!-- رمز عبور -->
            <div class="custom-input-group">
                <input type="password" name="password" placeholder="رمز عبور" required>
                <i class="bi bi-lock"></i>
            </div>

            <!-- تکرار رمز عبور -->
            <div class="custom-input-group">
                <input type="password" name="password_confirmation" placeholder="تکرار رمز عبور" required>
                <i class="bi bi-lock-fill"></i>
            </div>

            <button type="submit" class="btn-auth">
                ثبت نام و دریافت کد
                <i class="bi bi-check-lg me-1"></i>
            </button>
        </form>

        <div class="auth-footer">
            قبلاً ثبت نام کرده‌اید؟ <a href="{{ route('login') }}">وارد شوید</a>
        </div>
    </div>
@endsection
