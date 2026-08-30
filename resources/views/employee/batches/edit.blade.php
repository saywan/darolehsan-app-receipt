@extends('layouts.panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">

                {{-- هدر کارت --}}
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f0f0f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>ویرایش دسته قبض
                    </h5>
                    <a href="{{ route('employee.batches.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-arrow-right me-1"></i>بازگشت
                    </a>
                </div>

                <div class="card-body p-4">

                    {{-- نمایش خطاها --}}
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- فرم ویرایش --}}
                    {{-- نکته: روت update نیاز به ID دارد --}}
                    <form action="{{ route('employee.batches.update', $batch->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- تبدیل متد فرم به PUT --}}

                        <div class="row g-4">

                            {{-- ردیف ۱ --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">شماره شروع</label>
                                <input type="number" class="form-control" name="start_number" value="{{ old('start_number', $batch->start_number) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">شماره پایان</label>
                                <input type="number" class="form-control" name="end_number" value="{{ old('end_number', $batch->end_number) }}" required>
                            </div>

                            <hr class="text-secondary opacity-10 my-4">

                            {{-- ردیف ۲ --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">شماره فعلی (آخرین صادر شده)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control text-primary fw-bold" name="current_number" value="{{ old('current_number', $batch->current_number) }}" required>
                                    <span class="input-group-text bg-light small">تغییر دستی</span>
                                </div>
                                <div class="form-text text-muted small">تنها در صورت خرابی قبض فیزیکی تغییر دهید.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">وضعیت</label>
                                <select class="form-select" name="status">
                                    <option value="active" {{ $batch->status == 'active' ? 'selected' : '' }}>🟢 فعال</option>
                                    <option value="finished" {{ $batch->status == 'finished' ? 'selected' : '' }}>⚫ تمام شده</option>
                                    <option value="inactive" {{ $batch->status == 'inactive' ? 'selected' : '' }}>🔴 غیرفعال</option>
                                </select>
                            </div>

                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-warning text-dark py-3 fw-bold shadow-sm rounded-3">
                                <i class="bi bi-check-circle-fill me-2"></i> ذخیره تغییرات
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
