<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\NegarSmsService; // حتما ایمپورت باشد
use Illuminate\Support\Facades\Log; // برای لاگ کردن خطاها

use Illuminate\Support\Str;



class AuthController extends Controller
{

    protected $smsService;

    /**
     * تزریق وابستگی:
     * لاراول به صورت خودکار نسخه آماده شده (با تنظیمات) را از AppServiceProvider می‌آورد.
     */
    public function __construct(NegarSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * نمایش فرم دریافت شماره موبایل
     */
    public function showLoginForm()
    {
        // اگر کاربر همین الان لاگین است، فرم را نشان نده و بفرستش به داشبورد
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }


    /**
     * پردازش ورود و ثبت نام
     */
    public function login(Request $request)
    {
        // ۱. اعتبارسنجی فرمت شماره موبایل
        $request->validate([
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
        ], [
            'mobile.required' => 'لطفا شماره موبایل خود را وارد کنید.',
            'mobile.regex'    => 'فرمت شماره موبایل صحیح نیست (مثال: 09123456789).',
        ]);

        $mobile = $request->mobile;

        // ۲. جستجوی کاربر در دیتابیس
        $user = User::where('mobile', $mobile)->first();

        // --- سناریوی اول: کاربر قدیمی (ورود سریع) ---
        if ($user) {
            // ** اصلاح خودکار نقش کاربر اگر خالی باشد **
            if (empty($user->role)) {
                $user->role = 'employee';
                $user->save();
            }

            // بلافاصله لاگین کن
            Auth::login($user);
            $this->logActivity($user->id, 'LOGIN_QUICK', "ورود سریع کاربر قدیمی بدون OTP");

            // هدایت به داشبورد
            return redirect()->route('dashboard');
        }

        // --- سناریوی دوم: کاربر جدید (ثبت نام با OTP) ---

        // ساخت کاربر جدید
        $user = User::create([
            'mobile' => $mobile,
            'name' => 'کاربر جدید',
            'password' => Hash::make(Str::random(20)),
        ]);

        // تنظیم نقش کارمند برای کاربر جدید
        $user->role = 'employee';
        $user->save();

        // تولید کد تایید
        $otpCode = rand(10000, 99999);
        $expiresAt = Carbon::now()->addMinutes(2);

        // تلاش برای ارسال پیامک
        try {
            $message = "کد ورود به دارالاحسان: {$otpCode}";

            // استفاده از سرویس تزریق شده
            $this->smsService->sendOneToMany($message, [$user->mobile]);

            // ذخیره کد واقعی در دیتابیس
            $user->otp_code = $otpCode;
            $user->otp_expires_at = $expiresAt;
            $user->save();

            $this->logActivity($user->id, 'OTP_SENT', "کد تایید برای ثبت نام ارسال شد.");
            $flashMessage = 'کد تایید به موبایل شما ارسال شد.';
        } catch (\Exception $e) {
            // --- سناریوی قطعی پنل پیامک (Fallback) ---
            Log::error("SMS Service Failed: " . $e->getMessage());

            // تنظیم یک کد ثابت اضطراری
            $otpCode = 12345;

            $user->otp_code = $otpCode;
            $user->otp_expires_at = $expiresAt;
            $user->save();

            // پیام به کاربر که از کد ثابت استفاده کند
            $flashMessage = "سیستم پیامک موقتاً قطع است. لطفاً از کد {$otpCode} برای ورود استفاده کنید.";
        }

        // ذخیره ID کاربر در سشن برای مرحله بعد
        session(['verify_user_id' => $user->id]);

        // هدایت به صفحه تایید کد
        return redirect()->route('auth.verify')->with('message', $flashMessage);
    }

    /**
     * نمایش فرم تایید کد (Verify)
     */
    public function showVerifyForm()
    {
        if (!session()->has('verify_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('verify_user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $expiresAt = Carbon::parse($user->otp_expires_at);
        $now = Carbon::now();
        $secondsRemaining = $now->lessThan($expiresAt) ? $now->diffInSeconds($expiresAt) : 0;

        return view('auth.verify', [
            'mobile' => $user->mobile,
            'secondsRemaining' => $secondsRemaining
        ]);
    }

    /**
     * پردازش نهایی کد تایید (مخصوص ثبت نام)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:5',
        ], [
            'otp.digits' => 'کد تایید باید دقیقاً ۵ رقم باشد.',
            'otp.required' => 'لطفا کد تایید را وارد کنید.'
        ]);

        if (!session()->has('verify_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('verify_user_id'));

        // ۱. بررسی انقضا
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'زمان کد تایید به پایان رسیده است.']);
        }

        // ۲. بررسی صحت کد
        if ((int)$user->otp_code !== (int)$request->otp) {
            return back()->withErrors(['otp' => 'کد وارد شده صحیح نیست.']);
        }

        // ۳. تایید و ورود
        $user->mobile_verified_at = Carbon::now();
        $user->otp_code = null;

        // اطمینان از داشتن نقش
        if (empty($user->role)) {
            $user->role = 'employee';
        }

        $user->save();

        Auth::login($user);
        session()->forget('verify_user_id');

        $this->logActivity($user->id, 'REGISTER_SUCCESS', "ثبت نام و ورود موفق.");

        return redirect()->route('dashboard');
    }

    /**
     * ارسال مجدد (فقط در مرحله ثبت نام کاربرد دارد)
     */
    public function resendOtp()
    {
        if (!session()->has('verify_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('verify_user_id'));
        $newOtp = rand(10000, 99999);
        $user->otp_expires_at = Carbon::now()->addMinutes(2);

        try {
            $message = "کد تایید جدید: {$newOtp}";

            // استفاده از سرویس تزریق شده
            $this->smsService->sendOneToMany($message, [$user->mobile]);

            $user->otp_code = $newOtp;
            $user->save();
            return back()->with('message', 'کد تایید جدید ارسال شد.');
        } catch (\Exception $e) {
            Log::error("Resend SMS Failed: " . $e->getMessage());
            // در ارسال مجدد هم اگر پنل قطع بود کد ثابت میدهیم
            $user->otp_code = 12345;
            $user->save();
            return back()->with('message', 'خطا در ارسال پیامک. کد تایید شما: 12345');
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->logActivity(Auth::id(), 'LOGOUT', "خروج از سیستم.");
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function logActivity($userId, $action, $description)
    {
        try {
            if (class_exists(SystemLog::class)) {
                SystemLog::create([
                    'user_id'     => $userId,
                    'action'      => $action,
                    'description' => $description,
                    'ip_address'  => request()->ip(),
                    'device_info' => request()->userAgent()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("DB Log Error: " . $e->getMessage());
        }
    }

}
