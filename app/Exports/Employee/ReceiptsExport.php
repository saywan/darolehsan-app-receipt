<?php

namespace App\Exports\Employee;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Receipt; // مدل رسیدها
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceiptsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Receipt::query();

        // اعمال همان فیلترهایی که در کنترلر داریم
        if ($this->request->donor_name) {
            // فرض: فیلد نام پرداخت کننده donor_name است
            $query->where('donor_name', 'like', '%' . $this->request->donor_name . '%');
        }
        if ($this->request->start_date) {
             // تبدیل تاریخ شمسی به میلادی باید اینجا انجام شود (در اینجا فرض بر ورود میلادی یا استفاده از هلپر است)
            $query->whereDate('created_at', '>=', $this->request->start_date);
        }
        if ($this->request->end_date) {
            $query->whereDate('created_at', '<=', $this->request->end_date);
        }
        if ($this->request->type) {
            $query->where('type', $this->request->type);
        }

        return $query->latest()->get();
    }

    public function map($receipt): array
    {
        return [
            $receipt->id,
            $receipt->donor_name ?? 'ناشناس', // نام خیر
            number_format($receipt->amount) . ' ریال',
            $receipt->type, // نوع کمک (صدقات، نذورات و...)
            jdate($receipt->created_at)->format('%Y/%m/%d'), // تاریخ شمسی
            $receipt->description,
        ];
    }

    public function headings(): array
    {
        return [
            'شماره رسید',
            'نام خیر / نیکوکار',
            'مبلغ',
            'نوع کمک',
            'تاریخ پرداخت',
            'توضیحات',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}