@extends('layouts.panel')

@section('title', 'لیست رسیدهای غیرنقدی')
@section('header_title', 'مدیریت کمک‌های غیرنقدی')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bi bi-receipt-cutoff me-2 text-primary"></i>لیست رسیدهای ثبت شده
            </h5>
            <a href="{{ route('employee.non-cash-receipts.create') }}" class="btn btn-primary rounded-3">
                <i class="bi bi-plus-lg me-1"></i>ثبت رسید غیرنقدی جدید
            </a>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 15%;">شماره رسید</th>
                            <th style="width: 20%;">نام اهداکننده</th>
                            <th style="width: 15%;">شماره تماس</th>
                            <th style="width: 12%;">تاریخ رسید</th>
                            <th style="width: 15%;">ارزش کل (ریال)</th>
                            <th style="width: 8%;">وضعیت پیامک</th>
                            <th style="width: 10%;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-dark font-monospace">{{ $receipt->receipt_number }}</span></td>
                                <td class="fw-semibold text-dark">{{ $receipt->donor_name }}</td>
                                <td dir="ltr">{{ $receipt->donor_mobile }}</td>
                                <td>{{ $receipt->receipt_date ? verta($receipt->receipt_date)->format('Y/m/d') : '-' }}</td>
                                <td class="fw-bold text-success">{{ number_format($receipt->total_estimated_value ?? 0) }}</td>
                                <td>
                                    @if($receipt->sms_sent)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">ارسال شد</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">ارسال نشده</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('employee.non-cash-receipts.destroy', $receipt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این رسید اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    هیچ رسیدی تاکنون ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $receipts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
