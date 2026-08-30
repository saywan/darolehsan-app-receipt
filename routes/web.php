<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\ReceiptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\ReportController;
use App\Http\Controllers\Employee\NonCashReceiptController;


use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;

use App\Http\Controllers\Employee\ReceiptBatchController; // اضافه شد




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// روت‌های عمومی (مهمان)

Route::get('/', function () {
    return view('welcome');
})->name('home');


// نمایش فرم ورود (شامل ثبت نام خودکار)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// پردازش شماره موبایل
Route::get('/register', function () {
    return redirect()->route('login'); // ریدایرکت قدیمی‌ها به لاگین جدید
})->name('register'); // برای جلوگیری از خطای جاهایی که route('register') صدا زده شده

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// صفحه تایید کد (Verify)
Route::get('/verify', [AuthController::class, 'showVerifyForm'])->name('auth.verify');
Route::post('/verify', [AuthController::class, 'verify'])->name('auth.verify.submit');

// ارسال مجدد کد
Route::post('/verify/resend', [AuthController::class, 'resendOtp'])->name('auth.verify.resend');

// خروج
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');




// Route::middleware(['auth'])->get('/dashboard', function () {
//     $user = Illuminate\Support\Facades\Auth::user();

//     if ($user->role === 'admin') {
//         return redirect()->route('admin.dashboard');
//     }

//     if ($user->role === 'employee') {
//         return redirect()->route('employee.dashboard');
//     }

//     // اگر نقش نداشت یا نقش اشتباه بود:
//     Auth::logout();
//     return redirect()->route('login')->withErrors(['mobile' => 'حساب کاربری شما نقش دسترسی ندارد. با پشتیبانی تماس بگیرید.']);
// })->name('dashboard');
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = Illuminate\Support\Facades\Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard'); // <--- کلمه route نباید جا بیفتد
    }

    // پیش‌فرض: کارمند
    return redirect()->route('employee.dashboard'); // <--- کلمه route نباید جا بیفتد
})->name('dashboard');



// --- پنل کاربری (محافظت شده) ---
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // این روت می‌شود: route('admin.dashboard')
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // سایر روت‌های ادمین مثل مدیریت کاربران و ... اینجا اضافه می‌شوند
    });


Route::middleware(['auth', 'role:employee'])
    ->prefix('panel')
    ->name('employee.')
    ->group(function () {

        // این روت می‌شود: route('employee.dashboard')
        Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');

        // روت‌های ثبت فیش و ... اینجا اضافه می‌شوند

         // --- روت‌های صدور قبض ---
        Route::get('/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
        Route::post('/receipts', [ReceiptController::class, 'store'])->name(name: 'receipts.store');


    // روت‌های دسته قبض (جدید)
       Route::resource('batches', ReceiptBatchController::class);



    Route::get('/batches/{id}/receipts', [ReceiptBatchController::class, 'receipts'])->name('batches.receipts');



    Route::get('/receipts/{id}/edit', [ReceiptController::class, 'edit'])->name('receipts.edit');
    Route::put('/receipts/{id}', [ReceiptController::class, 'update'])->name('receipts.update');
    Route::delete('/receipts/{id}', [ReceiptController::class, 'destroy'])->name('receipts.destroy'); // برای ابطال




    // مدیریت صندوق صدقات
    Route::get('/boxes', [App\Http\Controllers\Employee\CharityBoxController::class, 'index'])->name('boxes.index');
    Route::get('/boxes/create', [App\Http\Controllers\Employee\CharityBoxController::class, 'create'])->name('boxes.create');
    Route::post('/boxes', [App\Http\Controllers\Employee\CharityBoxController::class, 'store'])->name('boxes.store');
    // فرم تخلیه صندوق
    Route::get('/boxes/{boxAllocation}/collect', [App\Http\Controllers\Employee\CharityBoxController::class, 'edit'])->name('boxes.edit'); // همان فرم تخلیه
    Route::put('/boxes/{boxAllocation}', [App\Http\Controllers\Employee\CharityBoxController::class, 'update'])->name('boxes.update');



    Route::get('/sms-logs', [App\Http\Controllers\Employee\SmsLogController::class, 'index'])->name('sms_logs.index');

    Route::post('/sms-logs/{log}/resend', [App\Http\Controllers\Employee\SmsLogController::class, 'resend'])->name('sms_logs.resend');





     // --- روت‌های جدید گزارشات مالی که باید اضافه کنید ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.excel');


    Route::prefix('non-cash-receipts')->name('non-cash-receipts.')->group(function () {
        Route::get('/', [NonCashReceiptController::class, 'index'])->name('index');
        Route::get('/create', [NonCashReceiptController::class, 'create'])->name('create');
        Route::post('/store', [NonCashReceiptController::class, 'store'])->name('store');
        Route::get('/{non_cash_receipt}', [NonCashReceiptController::class, 'show'])->name('show');
        Route::get('/{non_cash_receipt}/edit', [NonCashReceiptController::class, 'edit'])->name('edit');
        Route::put('/{non_cash_receipt}', [NonCashReceiptController::class, 'update'])->name('update');
        Route::delete('/{non_cash_receipt}', [NonCashReceiptController::class, 'destroy'])->name('destroy');
    });


    // روت چاپ/پیش‌نمایش قبض در صورت نیاز
    Route::get('non-cash-receipts/{non_cash_receipt}/print', [NonCashReceiptController::class, 'print'])
        ->name('non-cash-receipts.print');


});
