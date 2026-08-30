@extends('layouts.panel')

@section('title', 'گزارش لاگ پیامک‌ها')

@section('content')
<div class="container-fluid mt-4 mb-5">

    <!-- نمایش پیام‌های موفقیت یا خطا -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-envelope-open-text ms-2"></i> تاریخچه پیامک‌های سیستم</h5>
        </div>

        <div class="card-body">
            <!-- فرم جستجو و فیلتر -->
            <form action="{{ route('employee.sms_logs.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="جستجو در نام، شماره یا متن پیام..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">همه وضعیت‌ها</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>ارسال موفق</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>خطا در ارسال</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">همه رویدادها</option>
                            <option value="box_assigned" {{ request('type') == 'box_assigned' ? 'selected' : '' }}>تحویل صندوق</option>
                            <option value="box_collected" {{ request('type') == 'box_collected' ? 'selected' : '' }}>تخلیه صندوق</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> فیلتر</button>
                        @if(request()->hasAny(['search', 'status', 'type']))
                            <a href="{{ route('employee.sms_logs.index') }}" class="btn btn-outline-secondary mt-2 btn-sm">حذف فیلترها</a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- جدول نمایش لاگ‌ها -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>گیرنده / شماره</th>
                            <th>نوع رویداد</th>
                            <th>متن پیامک</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت / بروزرسانی</th>
                            <th>عملیات</th> <!-- ستون جدید -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + $logs->firstItem() - 1 }}</td>

                                <td class="text-nowrap">
                                    <div class="fw-bold">{{ $log->receiver_name ?? 'ناشناس' }}</div>
                                    <div class="text-muted" dir="ltr"><small>{{ $log->mobile }}</small></div>
                                </td>

                                <td class="text-center">
                                    @if($log->type == 'box_assigned')
                                        <span class="badge bg-info text-dark">تحویل صندوق</span>
                                    @elseif($log->type == 'box_collected')
                                        <span class="badge bg-primary">تخلیه صندوق</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->type }}</span>
                                    @endif
                                </td>

                                <td style="max-width: 250px;">
                                    <div class="text-truncate" title="{{ $log->message }}" style="white-space: pre-wrap; font-size: 0.9rem;">
                                        {{ $log->message }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($log->status == 'sent')
                                        <span class="badge bg-success"><i class="fas fa-check"></i> ارسال شده</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> خطا</span>
                                        @if($log->error_message)
                                            <div class="mt-1">
                                                <small class="text-danger" title="{{ $log->error_message }}" style="cursor: help;">مشاهده خطا</small>
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <td class="text-center text-nowrap" dir="ltr">
                                    @if(function_exists('verta'))
                                        <div style="font-size: 0.85rem;">{{ verta($log->created_at)->format('Y/m/d H:i') }}</div>
                                        @if($log->updated_at != $log->created_at)
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;" title="آخرین تلاش برای ارسال مجدد">
                                                <i class="fas fa-sync-alt"></i> {{ verta($log->updated_at)->format('H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        <small>{{ $log->created_at->format('Y/m/d H:i') }}</small>
                                    @endif
                                </td>

                                <!-- ستون دکمه عملیات ارسال مجدد -->
                                <td class="text-center">
                                      @if($log->status == 'failed')
                                        <form action="{{ route('employee.sms_logs.resend', $log->id) }}" method="POST" onsubmit="return confirm('آیا از ارسال مجدد این پیامک به شماره {{ $log->mobile }} اطمینان دارید؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning shadow-sm" title="تلاش دوباره برای ارسال">
                                                <i class="fas fa-redo-alt"></i> ارسال مجدد
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-double"></i> ارسال شده
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block" style="color: #dee2e6;"></i>
                                    هیچ لاگ پیامکی در سیستم ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- صفحه‌بندی -->
            <div class="d-flex justify-content-center mt-4">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection
