@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست رسیدهای کمک‌های غیرنقدی</h5>
            <a href="{{ route('employee.non-cash-receipts.create') }}" class="btn btn-light btn-sm text-primary fw-bold">
                <i class="fa fa-plus"></i> ثبت رسید جدید
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>شماره رسید</th>
                            <th>نام اهداکننده</th>
                            <th>شماره موبایل</th>
                            <th>تعداد اقلام</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $index => $receipt)
                            <tr>
                                <td>{{ $receipts->firstItem() + $index }}</td>
                                <td><span class="badge bg-secondary">{{ $receipt->receipt_number }}</span></td>
                                <td>{{ $receipt->donor_name }}</td>
                                <td>{{ $receipt->donor_mobile }}</td>
                                <td><span class="badge bg-info text-dark">{{ $receipt->items->count() }} قلم</span></td>
                                <td>{{ verta($receipt->created_at)->format('Y/m/d H:i') }}</td>
                                <td>
                                    <a href="{{ route('employee.non-cash-receipts.show', $receipt->id) }}" class="btn btn-sm btn-outline-primary" title="مشاهده">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted py-4">هیچ رسیدی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $receipts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
