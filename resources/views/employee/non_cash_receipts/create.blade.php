@extends('layouts.panel') {{-- نام لایوت اصلی پنل خود را اینجا قرار دهید --}}

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">ثبت رسید کمک‌های غیرنقدی</h5>
        </div>

        <div class="card-body">

            {{-- ۱. بخش نمایش پیام‌های سیستم (سشن‌ها) --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>موفق!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>خطا!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- ۲. بخش نمایش خطاهای اعتبارسنجی (Validation Errors) --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <p class="mb-2"><strong>لطفاً خطاهای زیر را برطرف کنید:</strong></p>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ۳. فرم اصلی --}}
            <form action="{{ route('employee.non-cash.store') }}" method="POST">
                @csrf

                <h6 class="text-primary mt-3 mb-3"><i class="fa fa-user"></i> اطلاعات اهداکننده</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="donor_name" class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                        <input type="text" name="donor_name" id="donor_name" class="form-control" value="{{ old('donor_name') }}" required placeholder="مثال: علی احمدی">
                    </div>

                    <div class="col-md-6">
                        <label for="donor_mobile" class="form-label">شماره موبایل <span class="text-danger">*</span></label>
                        <input type="text" name="donor_mobile" id="donor_mobile" class="form-control" value="{{ old('donor_mobile') }}" required placeholder="مثال: 09123456789">
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-primary"><i class="fa fa-box"></i> لیست اقلام اهدایی</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="addNewRow()">
                        <i class="fa fa-plus"></i> افزودن قلم جدید
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>عنوان کالا <span class="text-danger">*</span></th>
                                <th>تعداد/مقدار <span class="text-danger">*</span></th>
                                <th>واحد <span class="text-danger">*</span></th>
                                <th>ارزش تخمینی (تومان)</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTbody">
                            {{-- ردیف اول (پیش‌فرض) --}}
                            <tr>
                                <td>
                                    <input type="text" name="items[0][title]" class="form-control" required placeholder="مثال: برنج">
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control" required min="1" placeholder="10">
                                </td>
                                <td>
                                    <input type="text" name="items[0][unit]" class="form-control" required placeholder="مثال: کیلوگرم">
                                </td>
                                <td>
                                    <input type="text" name="items[0][estimated_value]" class="form-control money-format" placeholder="مثال: 500,000">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger disabled" title="حداقل یک ردیف الزامی است">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-start">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fa fa-save"></i> ثبت نهایی رسید
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary px-4">انصراف</a>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // شمارنده برای ایندکس آرایه اقلام در فرم
    let itemIndex = 1;

    // تابع افزودن ردیف جدید
    function addNewRow() {
        const tbody = document.getElementById('itemsTbody');
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <input type="text" name="items[${itemIndex}][title]" class="form-control" required>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" required min="1">
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][unit]" class="form-control" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][estimated_value]" class="form-control money-format">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        itemIndex++;
    }

    // تابع حذف ردیف
    function removeRow(btn) {
        // پیدا کردن تگ tr مربوطه و حذف آن
        const row = btn.closest('tr');
        row.remove();
    }
</script>
@endsection
