<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNonCashReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'donor_name' => 'required|string|max:255',
            'donor_mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            'national_code' => 'nullable|digits:10',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|string|max:50',
            'items.*.description' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'donor_name' => 'نام خیر',
            'donor_mobile' => 'شماره همراه',
            'national_code' => 'کد ملی',
            'receipt_date' => 'تاریخ دریافت',
            'items' => 'اقلام اهدایی',
            'items.*.item_name' => 'نام کالا',
            'items.*.quantity' => 'تعداد/مقدار',
            'items.*.unit' => 'واحد',
            'items.*.estimated_unit_price' => 'ارزش واحد تخمینی',
        ];
    }
}
