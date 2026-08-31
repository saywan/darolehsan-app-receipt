<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>موسسه خیریه دارالاحسان</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #eef2f6 0%, #dfe4ea 100%); /* گرادینت طوسی روشن */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            border: none;
            position: relative;
        }

        /* هدر آبی رنگ با انحنا */
        .auth-header {
            background-color: #0d6efd; /* آبی استاندارد و جذاب */
            color: white;
            padding: 40px 20px 50px; /* فضای پایین بیشتر برای انحنا */
            text-align: center;
            position: relative;
            border-bottom-left-radius: 50% 20px; /* ایجاد انحنای پایین */
            border-bottom-right-radius: 50% 20px;
        }

        .auth-header h4 {
            font-weight: 800;
            margin-top: 15px;
            font-size: 1.4rem;
        }

        .auth-header p {
            opacity: 0.8;
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .auth-icon {
            font-size: 2.5rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 15px;
            padding: 5px 12px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .auth-body {
            padding: 30px 30px 40px;
        }

        /* استایل اینپوت‌ها مشابه تصویر */
        .custom-input-group {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border: 1px solid transparent;
            transition: all 0.3s;
        }

        .custom-input-group:focus-within {
            border-color: #0d6efd;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        .custom-input-group input {
            border: none;
            background: transparent;
            box-shadow: none;
            width: 100%;
            padding: 8px;
            font-size: 0.9rem;
            color: #333;
        }

        .custom-input-group input::placeholder {
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .custom-input-group i {
            color: #9ca3af;
            font-size: 1.1rem;
            margin-left: 5px;
        }

        /* دکمه اصلی */
        .btn-auth {
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            font-weight: 700;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-auth:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            color: white;
        }

        .auth-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        .auth-footer a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 700;
        }

        .copyright {
            margin-top: 25px;
            color: #94a3b8;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        @yield('content')
    </div>

    <div class="copyright">
        &copy; ۲۰۲۶ سیستم اتوماسیون خیریه دارالاحسان
    </div>

</body>
</html>
