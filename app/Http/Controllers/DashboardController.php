<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminIndex()
    {
        return view('dashboard.index', [
            'user' => Auth::user(),
            'role_title' => 'مدیریت کل',
            'stats' => [
                'receipts_count' => 1250, // نمونه: باید از دیتابیس خوانده شود
                'today_receipts' => 45,
                'users_count' => 120
            ]
        ]);
    }

    public function employeeIndex()
    {
        return view('dashboard.index', [
            'user' => Auth::user(),
            'role_title' => 'پنل کارمندی',
            'stats' => [
                'receipts_count' => 32, // فیش‌های خود کارمند
                'this_month' => 12,
            ]
        ]);
    }
}
