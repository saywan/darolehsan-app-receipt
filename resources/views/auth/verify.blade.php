<!-- resources/views/auth/verify.blade.php -->
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تایید شماره موبایل - دارالاحسان</title>
    
    <!-- لود کردن فونت وزیرمتن (Vazirmatn) -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --primary-color: #0d47a1; /* آبی شرکتی تیره */
            --primary-light: #e3f2fd; /* آبی خیلی روشن برای پس‌زمینه */
            --text-color: #333;
            --border-color: #ccc;
            --focus-color: #2196f3;
        }

        body {
            /* اعمال فونت وزیر به کل صفحه */
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .verify-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 6px solid var(--primary-color);
        }

        .logo-area {
            margin-bottom: 25px;
        }
        
        .logo-area h2 {
            color: var(--primary-color);
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }

        p.description {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .mobile-number {
            font-weight: 800;
            color: var(--primary-color);
            direction: ltr;
            display: inline-block;
            font-size: 1.1rem;
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 6px;
        }

        /* استایل اینپوت‌های ۵ تایی */
        .otp-inputs {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 25px;
            direction: ltr; /* مهم برای ترتیب چپ به راست کد */
        }

        .otp-inputs input {
            width: 100%;
            height: 55px;
            font-size: 26px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            transition: all 0.3s;
            /* فونت اعداد */
            font-family: 'Vazirmatn', sans-serif; 
            font-weight: bold;
            color: #333;
            background: #fafafa;
        }

        .otp-inputs input:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(13, 71, 161, 0.1);
            transform: translateY(-2px);
        }

        .btn-verify {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Vazirmatn', sans-serif;
        }

        .btn-verify:hover {
            background-color: #002171;
        }

        .btn-verify:disabled {
            background-color: #cfd8dc;
            cursor: not-allowed;
        }

        .timer-section {
            margin-top: 25px;
            font-size: 0.9rem;
            color: #555;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border: 1px dashed #ccc;
        }
        
        #timer {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1rem;
            display: inline-block;
            min-width: 40px;
        }

        .resend-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 0.95rem;
        }

        .resend-link:hover {
            text-decoration: underline;
        }

        .change-number {
            margin-top: 20px;
            display: block;
            font-size: 0.85rem;
            color: #777;
            text-decoration: none;
            transition: color 0.2s;
        }

        .change-number:hover {
            color: var(--primary-color);
        }

        .error-msg {
            color: #d32f2f;
            font-size: 0.85rem;
            margin-bottom: 15px;
            display: block;
            font-weight: bold;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="verify-card">
        <div class="logo-area">
            <h2>تایید هویت</h2>
        </div>

        @if(session('message'))
            <div class="alert-success">
                {{ session('message') }}
            </div>
        @endif

        <p class="description">
            کد تایید ۵ رقمی به شماره 
            <span class="mobile-number">{{ $mobile }}</span>
            ارسال شد.
        </p>

        <form action="{{ route('auth.verify.submit') }}" method="POST" id="verifyForm">
            @csrf
            
            <input type="hidden" name="otp" id="final_otp">

            <div class="otp-inputs" id="otp-container">
                <input type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
                <input type="text" maxlength="1" inputmode="numeric">
            </div>

            @error('otp')
                <span class="error-msg">{{ $message }}</span>
            @enderror

            <button type="submit" class="btn-verify" id="submitBtn" disabled>تایید و ورود</button>
        </form>

        <div class="timer-section">
            <span id="timer-text">ارسال مجدد کد تا <span id="timer">{{ gmdate("i:s", $secondsRemaining) }}</span></span>
            
            <form action="{{ route('auth.verify.resend') }}" method="POST" id="resendForm" style="display: none;">
                @csrf
                <button type="submit" class="resend-link">ارسال مجدد کد</button>
            </form>
        </div>

        <a href="{{ route('login') }}" class="change-number">شماره اشتباه است؟ اصلاح شماره</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('otp-container');
            const inputs = container.querySelectorAll('input');
            const finalInput = document.getElementById('final_otp');
            const submitBtn = document.getElementById('submitBtn');

            inputs[0].focus(); // فوکوس روی اولین خانه هنگام لود

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    // تبدیل اعداد فارسی به انگلیسی (اختیاری)
                    e.target.value = e.target.value.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));

                    if (e.target.value.length > 1) {
                        e.target.value = e.target.value.slice(0, 1);
                    }
                    if (e.target.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                    updateFinalOtp();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    // استخراج فقط اعداد
                    const digits = text.replace(/[^0-9۰-۹]/g, '').split('');
                    
                    inputs.forEach((input, i) => {
                        if (digits[i]) {
                            // تبدیل فارسی به انگلیسی
                             let val = digits[i].replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                            input.value = val;
                        }
                    });
                    updateFinalOtp();
                    // فوکوس
                    const lastIndex = Math.min(digits.length, inputs.length) - 1;
                    if(lastIndex >= 0) inputs[lastIndex].focus();
                });
            });

            function updateFinalOtp() {
                let code = '';
                let filledCount = 0;
                inputs.forEach(input => {
                    code += input.value;
                    if(input.value) filledCount++;
                });
                finalInput.value = code;

                if (filledCount === 5) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }

            // تایمر
            let seconds = {{ $secondsRemaining }};
            const timerElement = document.getElementById('timer');
            const timerText = document.getElementById('timer-text');
            const resendForm = document.getElementById('resendForm');

            function updateTimerDisplay() {
                 const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                 const s = (seconds % 60).toString().padStart(2, '0');
                 timerElement.innerText = `${m}:${s}`;
            }

            if (seconds > 0) {
                updateTimerDisplay();
                const interval = setInterval(() => {
                    seconds--;
                    updateTimerDisplay();

                    if (seconds <= 0) {
                        clearInterval(interval);
                        timerText.style.display = 'none';
                        resendForm.style.display = 'inline-block';
                    }
                }, 1000);
            } else {
                timerText.style.display = 'none';
                resendForm.style.display = 'inline-block';
            }
        });
    </script>
</body>
</html>
