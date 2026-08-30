<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم رسید ساز دارالاحسان</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Vazirmatn -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/misc/Farsi-Digits/font-face.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #e0f7fa 0%, #80deea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Glassmorphism Logic */
        .glass-card {
            background: rgba(255, 255, 255, 0.65);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2rem;
            width: 100%;
        }

        /* Floating Labels (Material Design) Override */
        .form-floating > .form-control,
        .form-floating > .form-select {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 12px;
        }

        .form-floating > .form-control:focus {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
            border-color: #0dcaf0;
        }

        .btn-primary-glass {
            background: linear-gradient(45deg, #0288d1, #26c6da);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
            border-radius: 12px;
            padding: 12px 24px;
            transition: all 0.3s ease;
        }

        .btn-primary-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background: linear-gradient(45deg, #0277bd, #00bcd4);
        }

        /* Navbar for dashboard */
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-bottom: 20px;
            padding: 10px 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Titles */
        h1, h2, h3, h4, h5, h6 {
            color: #01579b; /* Dark Blue based on DarolEhsan logo */
            font-weight: 700;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            font-size: 3rem;
            color: #0288d1;
        }

    </style>
</head>
<body>

    <div class="container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
