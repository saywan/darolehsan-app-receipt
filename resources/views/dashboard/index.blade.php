<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد {{ $role_title }} - دارالاحسان</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <!-- آیکون‌ها -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0d47a1;       /* آبی اصلی */
            --secondary: #1e88e5;     /* آبی روشن‌تر */
            --bg-color: #f3f4f6;      /* خاکستری خیلی روشن */
            --sidebar-bg: #ffffff;
            --text-main: #1f2937;
            --text-light: #6b7280;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            box-sizing: border-box;
            outline: none;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
            color: var(--text-main);
        }

        /* --- Sidebar --- */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            border-left: 1px solid #e5e7eb;
            transition: all 0.3s;
            z-index: 100;
        }

        .logo-box {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #f0f0f0;
            color: var(--primary);
            font-weight: 800;
            font-size: 1.2rem;
        }

        .logo-box i { margin-left: 10px; }

        .menu {
            list-style: none;
            padding: 20px 15px;
            margin: 0;
            flex-grow: 1;
        }

        .menu li { margin-bottom: 8px; }

        .menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            color: var(--text-light);
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .menu a i {
            width: 25px;
            font-size: 1.1rem;
            margin-left: 10px;
        }

        .menu a:hover, .menu a.active {
            background-color: #e3f2fd;
            color: var(--primary);
        }

        .user-info-sidebar {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 10px;
        }

        .user-details h4 { margin: 0; font-size: 0.9rem; }
        .user-details span { font-size: 0.75rem; color: var(--text-light); }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .header {
            height: 70px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .header h2 { font-size: 1.1rem; margin: 0; }
        
        .date-display {
            font-size: 0.9rem;
            color: var(--text-light);
            background: #f9fafb;
            padding: 5px 15px;
            border-radius: 20px;
        }

        .dashboard-container {
            padding: 30px;
        }

        /* --- Stats Cards --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-info h3 {
            margin: 0;
            font-size: 2rem;
            color: var(--text-main);
        }

        .card-info p {
            margin: 5px 0 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .bg-blue-soft { background: #e3f2fd; color: #1976d2; }
        .bg-green-soft { background: #e8f5e9; color: #388e3c; }
        .bg-purple-soft { background: #f3e5f5; color: #7b1fa2; }
        .bg-orange-soft { background: #fff3e0; color: #f57c00; }

        /* --- Welcome Section --- */
        .welcome-box {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(13, 71, 161, 0.2);
        }

        .welcome-box h1 { margin: 0 0 10px; font-size: 1.5rem; }
        .welcome-box p { margin: 0; opacity: 0.9; }

        /* دایره‌های تزیینی */
        .circle-deco {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .c1 { width: 150px; height: 150px; top: -50px; left: -50px; }
        .c2 { width: 100px; height: 100px; bottom: -30px; left: 50px; }

        /* دکمه خروج */
        .logout-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            margin-right: auto;
        }
        .logout-btn:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                right: -260px;
                height: 100%;
            }
            .sidebar.open { right: 0; }
            .header { padding: 0 15px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-box">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <span>خیریه دارالاحسان</span>
        </div>

        <ul class="menu">
            <li>
                <a href="#" class="active">
                    <i class="fa-solid fa-gauge-high"></i>
                    داشبورد
                </a>
            </li>
            
            @if(auth()->user()->role == 'admin')
            <li>
                <a href="#">
                    <i class="fa-solid fa-users"></i>
                    مدیریت کاربران
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    گزارشات مالی
                </a>
            </li>
            @endif

            <li>
                <a href="#">
                    <i class="fa-solid fa-receipt"></i>
                    فیش‌های ثبت شده
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fa-solid fa-gear"></i>
                    تنظیمات حساب
                </a>
            </li>
        </ul>

        <div class="user-info-sidebar">
            <div class="avatar">
                {{ mb_substr($user->name ?? 'کاربر', 0, 1) }}
            </div>
            <div class="user-details">
                <h4>{{ $user->name ?? 'کاربر ناشناس' }}</h4>
                <span>{{ $role_title }}</span>
            </div>
            <form action="{{ route('auth.logout') }}" method="POST" style="margin-right: auto;">
                @csrf
                <button type="submit" class="logout-btn" title="خروج">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <h2>{{ $role_title }}</h2>
            <div class="date-display">
                {{ \Carbon\Carbon::now()->locale('fa')->isoFormat('dddd، D MMMM YYYY') }}
            </div>
        </header>

        <div class="dashboard-container">
            
            <!-- Welcome Banner -->
            <div class="welcome-box">
                <div class="circle-deco c1"></div>
                <div class="circle-deco c2"></div>
                <h1>سلام، {{ $user->name ?? 'همکار گرامی' }} 👋</h1>
                <p>به پنل کاربری خوش آمدید. آخرین ورود شما: {{ \Carbon\Carbon::now()->subMinutes(2)->format('H:i') }}</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                
                <div class="card">
                    <div class="card-info">
                        <h3>{{ $stats['receipts_count'] ?? 0 }}</h3>
                        <p>کل فیش‌های ثبت شده</p>
                    </div>
                    <div class="card-icon bg-blue-soft">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>{{ $stats['today_receipts'] ?? 0 }}</h3>
                        <p>فیش‌های امروز</p>
                    </div>
                    <div class="card-icon bg-green-soft">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>{{ $stats['users_count'] ?? '---' }}</h3>
                        <p>تعداد حامیان</p>
                    </div>
                    <div class="card-icon bg-purple-soft">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>{{ \Carbon\Carbon::now()->format('H:i') }}</h3>
                        <p>آخرین فعالیت</p>
                    </div>
                    <div class="card-icon bg-orange-soft">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </div>

            </div>

            <!-- اینجا می‌توانید جدول یا محتوای دیگری اضافه کنید -->
            <!-- <div style="background: white; padding: 20px; border-radius: 16px; min-height: 200px;">
                <h3 style="margin-top: 0;">آخرین فیش‌های دریافتی</h3>
                 محل قرارگیری جدول
            </div> -->

        </div>
    </main>

</body>
</html>
