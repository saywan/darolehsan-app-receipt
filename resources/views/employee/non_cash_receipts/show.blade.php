@extends('layouts.employee')

@section('title', 'رسید کمک غیرنقدی - ' . $receipt->receipt_number)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('employee.non_cash_receipts.index') }}" class="btn btn-secondary">بازگشت به لیست</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> چاپ رسید</button>
    </div>

    <div class="card shadow border p-4 print-area bg-white">
        <div class="row align-items-center border-bottom pb-3 mb-3">
            <div class="col-8">
                <h4 class="fw-bold mb-1">خیریه دارالاحسان</h4>
                <div class="text-muted">رسید دریافت کمک‌های غیرنقدی و اهدایی</div>
            </div>
            <div class="col-4 text-end">
                <div><strong>شماره رسید:</strong> {{ $receipt->receipt_number }}</div>
                <div><strong>تاریخ:</strong> {{ \Morilog\Jalali\Jalalian::fromCarbon($receipt->donation_date)->format('Y/m/d') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6"><strong>اهداکننده:</strong> {{ $receipt->donor_name }}</div>
            <div class="col-6"><strong>شماره تماس:</strong> {{ $receipt->donor_mobile }}</div>
            @if($receipt->donor_national_code)
                <div class="col-6 mt-2"><strong>کد ملی:</strong> {{ $receipt->donor_national_code }}</div>
            @endif
            @if($receipt->description)
                <div class="col-12 mt-2"><strong>توضیحات:</strong> {{ $receipt->description }}</div>
            @endif
        </div>

        <table class="table table-bordered text-center align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>ردیف</th>
                    <th>شرح کالا / خدمات</th>
                    <th>دسته‌بندی</th>
                    <th>مقدار / تعداد</th>
                    <th>واحد</th>
                    <th>ارزش تخمینی (تومان)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item_title }}</td>
                        <td>{{ $item->category ?? '-' }}</td>
                        <td>{{ (float)$item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ $item->estimated_value ? number_format($item->estimated_value) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="5" class="text-end">جمع کل ارزش تخمینی:</td>
                    <td>{{ number_format($receipt->total_estimated_value) }} تومان</td>
                </tr>
            </tfoot>
        </table>

        <div class="row text-center mt-5 pt-4">
            <div class="col-6">
                <p>امضاء و اثر انگشت اهداکننده</p>
            </div>
            <div class="col-6">
                <p>امضاء و مهر متصدی خیریه</p>
                <small class="text-muted">ثبت‌شده توسط: {{ $receipt->user->name ?? 'کارمند' }}</small>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: #fff !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection
