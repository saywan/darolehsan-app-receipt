<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش مالی جامع</title>
    <style>
        body {
            font-family: 'Tahoma', 'Arial', sans-serif; 
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #222;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #555; }
        .meta-box {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .meta-box table { width: 100%; border: none; margin: 0;}
        .meta-box td { border: none; padding: 2px; text-align: right; font-size: 11px; font-weight: bold;}
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        table.data-table th { background-color: #eee; font-weight: bold; }
        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-left { text-align: left; }
        
        .total-row { font-weight: bold; background-color: #e8f5e9; }
        .footer { text-align: center; font-size: 9px; margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px;}
    </style>
</head>
<body>

    <div class="header">
        <h1>موسسه خیریه دارالاحسان</h1>
        <p>گزارش ریز تراکنش‌ها و درآمدهای ثبت شده</p>
    </div>

    <div class="meta-box">
        <table>
            <tr>
                <td width="50%">تاریخ تهیه گزارش: {{ \Carbon\Carbon::now()->format('Y/m/d H:i') }}</td>
                <td width="50%" class="text-left">شرایط فیلتر: {{ $filterDescription }}</td>
            </tr>
            <tr>
                <td>مجموع کل درآمدهای این گزارش: {{ number_format($totalAmountRials / 10) }} تومان</td>
                <td class="text-left">تعداد کل رکوردها: {{ count($receipts) }} عدد</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">ردیف</th>
                <th width="12%">شماره سریال</th>
                <th width="20%">نام خیّر</th>
                <th width="15%">نوع کمک</th>
                <th width="13%">نحوه پرداخت</th>
                <th width="15%">تاریخ رسید</th>
                <th width="20%">مبلغ (تومان)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipts as $index => $receipt)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $receipt->serial_number ?? '---' }}</td>
                    <td class="text-right">{{ $receipt->donor_name ?? 'نامشخص' }}</td>
                    <td>{{ $receipt->help_type ?? '---' }}</td>
                    <td>{{ $receipt->payment_type ?? '---' }}</td>
                    <td dir="ltr">{{ $receipt->receipt_date ? \Carbon\Carbon::parse($receipt->receipt_date)->format('Y/m/d') : '---' }}</td>
                    
                    {{-- تبدیل ریال به تومان --}}
                    <td class="text-left">{{ number_format($receipt->amount_rials / 10) }}</td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="6" class="text-right">جمع کل مبالغ (تومان):</td>
                <td class="text-left">{{ number_format($totalAmountRials / 10) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        این سند از سیستم جامع مدیریت خیریه دارالاحسان استخراج شده است و بدون مهر و امضا فاقد اعتبار قانونی می‌باشد.
    </div>

</body>
</html>
