<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // در اینجا آمارهای واقعی دیتابیس را بعدا اضافه می‌کنیم
        $stats = [
            'total_users'    => User::count(),
            'total_receipts' => 0, // فعلا صفر تا مدل فیش ساخته شود
            'total_amount'   => 0,
        ];

        return view('admin.dashboard.index', [
            'user' => Auth::user(),
            'role_title' => 'مدیریت کل سیستم',
            'stats' => $stats
        ]);
    }
}
