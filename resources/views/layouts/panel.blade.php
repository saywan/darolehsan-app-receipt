<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'داشبورد') | دارالاحسان</title>

    <!-- لود کردن بوت‌استرپ نسخه RTL -->
    <link rel="stylesheet" href="{{ asset("assets/css/bootstrap.rtl.min.css") }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f3f4f6;
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        /* --- تنظیمات سایدبار (منوی راست دسکتاپ) --- */
        .sidebar {
            width: 280px;
            background: #ffffff;
            height: 100vh; /* ارتفاع کامل */
            position: fixed;
            top: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease-in-out;
            border-left: 1px solid rgba(0,0,0,0.05);
            display: flex;          /* استفاده از فلکس باکس */
            flex-direction: column; /* چیدمان ستونی */
            box-shadow: -4px 0 20px rgba(0,0,0,0.02);
        }

        /* بخش لوگو */
        .sidebar-header {
            padding: 25px 30px 20px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
            flex-shrink: 0; /* جلوگیری از جمع شدن */
        }

        /* بخش منوها (اسکرول‌دار) */
        .sidebar-content {
            flex-grow: 1;     /* پر کردن فضای خالی */
            overflow-y: auto; /* اسکرول در صورت نیاز */
            padding: 15px 0;
        }

        /* اسکرول بار زیبا و باریک برای منو */
        .sidebar-content::-webkit-scrollbar { width: 4px; }
        .sidebar-content::-webkit-scrollbar-track { background: transparent; }
        .sidebar-content::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

        /* بخش خروج (چسبیده به پایین) */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #f1f5f9;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .main-content {
            margin-right: 280px;
            margin-left: 0;
            transition: all 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: #64748b;
            text-decoration: none;
            transition: 0.2s;
            font-weight: 500;
            margin: 4px 16px;
            border-radius: 12px;
        }

        .sidebar-link:hover {
            color: #0f172a;
            background-color: #f1f5f9;
        }

        .sidebar-link.active {
            color: #2563eb;
            background-color: #eff6ff;
            font-weight: 700;
        }

        .sidebar-link i {
            margin-left: 12px;
            font-size: 1.3rem;
        }

        /* استایل دکمه خروج دسکتاپ (زیبا و مدرن) */
        .btn-logout-desktop {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background-color: #fef2f2; /* پس زمینه قرمز خیلی روشن */
            color: #ef4444;            /* متن قرمز */
            border: 1px solid #fee2e2;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.05);
        }

        .btn-logout-desktop:hover {
            background-color: #fee2e2;
            color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        .top-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }

        /* ========================================= */
        /* --- استایل‌های موبایل (حفظ کد قبلی) --- */
        /* ========================================= */
        .mobile-bottom-nav { display: none; }

        @media (max-width: 992px) {
            .sidebar { right: -280px; }
            .sidebar.show { right: 0; }
            .main-content { margin-right: 0; padding-bottom: 90px; }
            .overlay {
                display: none;
                position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.4); backdrop-filter: blur(3px); z-index: 999;
            }
            .overlay.show { display: block; }

            .mobile-bottom-nav {
                display: flex;
                position: fixed; bottom: 0; left: 0; right: 0;
                background-color: #ffffff;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
                z-index: 1050;
                justify-content: space-around; align-items: center;
                padding: 10px 5px;
                padding-bottom: calc(10px + env(safe-area-inset-bottom));
                border-top-left-radius: 24px; border-top-right-radius: 24px;
            }

            .nav-item-mobile {
                display: flex; flex-direction: column; align-items: center; justify-content: center;
                text-decoration: none; color: #94a3b8; font-size: 0.65rem; font-weight: 600;
                flex: 1; position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .nav-item-mobile i { font-size: 1.4rem; margin-bottom: 4px; transition: transform 0.3s; }
            .nav-item-mobile:active i { transform: scale(0.85); }
            .nav-item-mobile.active { color: #2563eb; }
            .nav-item-mobile.active i { transform: translateY(-4px) scale(1.15); color: #2563eb; text-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); }
            .nav-item-mobile.active::after {
                content: ''; position: absolute; bottom: -6px; width: 5px; height: 5px;
                background-color: #2563eb; border-radius: 50%; animation: popIn 0.3s ease forwards;
            }
            .nav-item-mobile.logout-btn-mobile { color: #ef4444; }
            .nav-item-mobile.logout-btn-mobile i {
                color: #ef4444; background: #fef2f2; width: 40px; height: 40px;
                display: flex; align-items: center; justify-content: center;
                border-radius: 50%; margin-bottom: 2px;
            }
            @keyframes popIn { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        }
    </style>
</head>
<body>

    <div class="overlay" id="sidebarOverlay"></div>

    <!-- سایدبار (منوی راست دسکتاپ) -->
    <nav class="sidebar" id="sidebar">
        <!-- 1. هدر سایدبار -->
        <div class="sidebar-header">
            <div class="bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-2 text-primary">
                <i class="bi bi-heart-fill fs-4"></i>
            </div>
            <h6 class="fw-bold text-dark mb-0">خیریه دارالاحسان</h6>
            <small class="text-muted" style="font-size: 0.75rem;">پنل کاربری همکاران</small>
        </div>

        <!-- 2. محتوای منو (وسط - اسکرول دار) -->
        <div class="sidebar-content">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                <span>داشبورد</span>
            </a>

            <a href="{{ route('employee.batches.index') }}" class="sidebar-link {{ request()->routeIs('employee.batches.*') || request()->routeIs('employee.receipts.*') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i>
                <span>مدیریت قبوض</span>
            </a>

            <a href="{{ route('employee.boxes.index') }}" class="sidebar-link {{ request()->routeIs('employee.boxes.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i>
                <span>صندوق قلک </span>
            </a>
 <a class="sidebar-link {{ request()->routeIs('employee.non-cash-receipts.*') ? 'active' : '' }}"
       href="{{ route('employee.non-cash-receipts.index') }}">
        <i class="bi bi-box-seam me-2"></i>
        <span>رسیدهای غیرنقدی</span>
    </a>

          {{-- <a class="sidebar-link {{ request()->routeIs('employee.non-cash-receipts.*') ? 'active' : '' }}"
       href="{{ route('employee.non-cash-receipts.index') }}">
        <i class="bi bi-box-seam me-2"></i>
        <span>کمک‌های غیرنقدی</span>
    </a> --}}

            <a href="{{ route('employee.sms_logs.index') }}" class="sidebar-link {{ request()->routeIs('employee.sms_logs.*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-text"></i>
                <span> لاگ پیامک </span>
            </a>

             <a href="{{ route('employee.reports.index') }}" class="sidebar-link {{ request()->routeIs('employee.reports.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data"></i>
                <span>گزارشات مالی</span>
            </a>

             <!-- اینجا می‌توانید منوهای بیشتری اضافه کنید، اسکرول می‌شود و روی دکمه خروج نمی‌افتد -->
        </div>

        <!-- 3. فوتر سایدبار (دکمه خروج ثابت) -->
        <div class="sidebar-footer">
             <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-logout-desktop">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                    <span>خروج از سیستم</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- محتوای اصلی -->
    <div class="main-content">
        <header class="top-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border shadow-sm rounded-circle" id="sidebarToggle" style="width:40px; height:40px; padding:0;">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark fs-6 d-none d-sm-block">
                    @yield('header_title', 'پیشخوان')
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3 bg-white px-3 py-1 rounded-pill border shadow-sm">
                <div class="text-start lh-sm d-none d-md-block ps-2">
                    <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">{{ Auth::user()->name ?? 'کاربر' }}</span>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ Auth::user()->mobile ?? '' }}</small>
                </div>
                <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center border" style="width: 38px; height: 38px;">
                    <i class="bi bi-person fs-5"></i>
                </div>
            </div>
        </header>

        <div class="p-4 p-md-5">
            @yield('content')
        </div>
    </div>

    <!-- منوی موبایل (بدون تغییر) -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('dashboard') }}" class="nav-item-mobile {{ request()->routeIs('dashboard') || request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ request()->routeIs('dashboard') ? '-fill' : '' }}"></i>
            <span>پیشخوان</span>
        </a>
        <a href="{{ route('employee.batches.index') }}" class="nav-item-mobile {{ request()->routeIs('employee.batches.*') || request()->routeIs('employee.receipts.*') ? 'active' : '' }}">
            <i class="bi bi-receipt{{ request()->routeIs('employee.batches.*') ? '-cutoff' : '' }}"></i>
            <span>قبوض</span>
        </a>
        <a href="{{ route('employee.reports.index') }}" class="nav-item-mobile {{ request()->routeIs('employee.reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart{{ request()->routeIs('employee.reports.*') ? '-fill' : '' }}"></i>
            <span>گزارشات</span>
        </a>
        <a href="{{ route('employee.boxes.index') }}" class="nav-item-mobile {{ request()->routeIs('employee.boxes.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam{{ request()->routeIs('employee.boxes.*') ? '-fill' : '' }}"></i>
            <span>صندوق</span>
        </a>

       <a class="nav-link {{ request()->routeIs('employee.non-cash-receipts.*') ? 'active' : '' }}"
       href="{{ route('employee.non-cash-receipts.index') }}">
        <i class="bi bi-box-seam me-2"></i>
        <span>کمک‌های غیرنقدی</span>
    </a>



        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0 d-flex" style="flex: 1;">
            @csrf
            <button type="submit" class="nav-item-mobile logout-btn-mobile w-100 bg-transparent border-0">
                <i class="bi bi-power"></i>
                <span>خروج</span>
            </button>
        </form>
    </nav>

    <script src="{{ asset("assets/js/bootstrap.bundle.min.js") }}"></script>
    <script>
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleMenu() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        if(toggleBtn) {
            toggleBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        }
    </script>
     @stack('scripts')
</body>
</html>
