@extends('layouts.auth')

@section('content')
    <!-- استایل‌های اختصاصی برای مچ شدن دقیق با تصویر -->
    <style>
        .welcome-icon-box {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            width: 65px;
            height: 65px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: white;
            backdrop-filter: blur(5px);
        }

        .welcome-title {
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .welcome-subtitle {
            font-size: 0.85rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .welcome-text {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.8;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        /* دکمه ورود (توپر) */
        .btn-login-custom {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .btn-login-custom:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            color: white;
        }

        /* دکمه ثبت نام (توخالی) */
        .btn-register-custom {
            background-color: white;
            color: #0d6efd;
            border: 1px solid #0d6efd;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-register-custom:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
        }

        /* اصلاح انحنای هدر برای این صفحه */
        .auth-header {
            padding-bottom: 60px; /* افزایش ارتفاع برای زیبایی */
        }
    </style>

    <div class="auth-header">
        <!-- آیکون قلب داخل باکس شفاف -->
        <div class="welcome-icon-box">
            <i class="bi bi-heart-fill"></i>
        </div>

        <h4 class="welcome-title">خیریه دارالاحسان</h4>
        <p class="welcome-subtitle">سامانه جامع صدور رسید دیجیتال</p>
    </div>

    <div class="auth-body text-center pt-4">

        <p class="welcome-text">
            به سامانه اتوماسیون خوش آمدید. برای صدور رسید یا مدیریت حساب کاربری خود، لطفاً وارد شوید.
        </p>

        @auth
            <!-- حالت لاگین شده -->
            <div class="alert alert-primary border-0 bg-primary bg-opacity-10 text-primary small mb-4 rounded-3">
                <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }} عزیز، خوش آمدید
            </div>



            <a href="{{  route('dashboard') }}" class="btn-login-custom text-decoration-none">
                <i class="bi bi-speedometer2 ms-2"></i>
                ورود به داشبورد
            </a>
        @else
            <!-- دکمه ورود -->
            <a href="{{ route('login') }}" class="btn-login-custom text-decoration-none">
                <i class="bi bi-box-arrow-in-left ms-2" style="font-size: 1.1rem;"></i>
                ورود به حساب
            </a>

            <!-- دکمه ثبت نام -->
            {{-- <a href="{{ route('register') }}" class="btn-register-custom text-decoration-none">
                <i class="bi bi-person-plus ms-2" style="font-size: 1.1rem;"></i>
                ثبت نام کاربر جدید
            </a> --}}
        @endauth

    </div>
@endsection
