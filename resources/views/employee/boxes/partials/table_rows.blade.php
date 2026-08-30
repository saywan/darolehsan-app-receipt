@forelse($allocations as $allocation)
    <tr style="border-bottom: 1px solid #F8FAFC;">

        <td class="fw-bold" style="color: #1E293B;">
            {{ $allocation->charityBox->code ?? '---' }}
        </td>

        <td>
            @if(isset($allocation->charityBox) && $allocation->charityBox->type == 'plastic')
                <span class="badge text-dark" style="background-color: #FFC107; border-radius: 50px; padding: 6px 16px; font-weight: normal;">پلاستیکی</span>
            @else
                <span class="badge" style="background-color: #E2E8F0; color: #475569; border-radius: 50px; padding: 6px 16px; font-weight: normal;">شیشه‌ای</span>
            @endif
        </td>

        <td>
            <div class="d-flex align-items-center justify-content-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                    <i class="fas fa-user text-muted" style="font-size: 12px;"></i>
                </div>
                <span style="color: #334155; font-weight: 500;">
                    {{ $allocation->applicant_name ?? $allocation->receiver_name ?? '---' }}
                </span>
            </div>
        </td>

        <td dir="ltr" style="color: #475569;">
            {{ $allocation->applicant_mobile ?? $allocation->receiver_phone ?? '---' }}
        </td>

        <td style="color: #64748B;">
            @if($isArchived)
                <div class="d-flex flex-column align-items-center">
                    <span>{{ verta($allocation->collected_at)->format('Y/m/d') }}</span>
                    <strong class="text-success small mt-1">{{ number_format($allocation->amount) }} ریال</strong>
                </div>
            @else
                {{ $allocation->assigned_at ? verta($allocation->assigned_at)->format('Y/m/d') : '---' }}
            @endif
        </td>

        <!-- ستون عملیات -->
        <td>
            <div class="d-flex align-items-center justify-content-center gap-2">

                <!-- دکمه مشاهده اطلاعات -->
                <button type="button" class="btn btn-light shadow-sm d-flex align-items-center gap-1 transition-all" data-bs-toggle="modal" data-bs-target="#infoModal{{ $allocation->id }}" style="border-radius: 50px; padding: 6px 14px; font-size: 12px; border: 1px solid #CBD5E1; color: #3B82F6; background-color: #fff;">
                    <i class="fas fa-eye"></i> پرونده
                </button>

                <!-- دکمه تخلیه یا وضعیت -->
                @if($isArchived)
                    <span class="badge bg-success bg-opacity-10 text-success d-flex align-items-center gap-1" style="border-radius: 50px; padding: 6px 14px; font-weight: 500; font-size: 12px;">
                        <i class="fas fa-check-circle"></i> تسویه شده
                    </span>
                @else
                    <a href="{{ route('employee.boxes.edit', $allocation->id) }}" class="btn btn-success shadow-sm d-flex align-items-center gap-1 transition-all" style="border-radius: 50px; padding: 6px 14px; font-size: 12px; background-color: #10B981; border: none;">
                        <i class="fas fa-hand-holding-usd"></i> تخلیه
                    </a>
                @endif
            </div>

            <!-- مودال اطلاعات کامل (RTL و گروه‌بندی شده) -->
            <div class="modal fade" id="infoModal{{ $allocation->id }}" tabindex="-1" aria-hidden="true" style="direction: rtl;">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">

                        <!-- هدر مودال -->
                        <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center" style="background-color: #f8fafc; padding: 1.2rem 1.5rem;">
                            <h5 class="modal-title fw-bold d-flex align-items-center gap-2 m-0" style="color: #1e293b; font-size: 1.1rem;">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 38px; height: 38px;">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                پرونده کامل صندوق
                            </h5>
                            <button type="button" class="btn-close m-0 me-auto" data-bs-dismiss="modal" aria-label="Close" style="margin-left: unset;"></button>
                        </div>

                        <!-- بدنه مودال (با استایل‌های اختصاصی برای جلوگیری از به هم ریختگی) -->
                        <div class="modal-body p-4" style="text-align: right; background-color: #fff;">

                            <!-- گروه 1: مشخصات صندوق -->
                            <div class="mb-4 p-4 border" style="background-color: #f8fafc; border-radius: 12px; border-color: #e2e8f0 !important;">
                                <h6 class="fw-bold mb-4 d-flex align-items-center" style="color: #3b82f6; border-bottom: 2px solid #eff6ff; padding-bottom: 12px;">
                                    <i class="fas fa-box-open ms-2"></i> مشخصات فیزیکی صندوق
                                </h6>
                                <div class="row gy-4">
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">کد صندوق سیستم</span>
                                        <strong class="d-block text-dark fs-5" style="letter-spacing: 1px;">{{ $allocation->charityBox->code ?? '---' }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">نوع صندوق</span>
                                        @if(isset($allocation->charityBox) && $allocation->charityBox->type == 'plastic')
                                            <span class="badge text-dark px-3 py-2" style="background-color: #fef08a; border: 1px solid #fde047; font-size: 13px;">پلاستیکی (یکبار مصرف)</span>
                                        @else
                                            <span class="badge text-secondary px-3 py-2" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-size: 13px;">شیشه‌ای (دائمی)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- گروه 2: مشخصات نیکوکار -->
                            <div class="mb-4 p-4 border" style="background-color: #f8fafc; border-radius: 12px; border-color: #e2e8f0 !important;">
                                <h6 class="fw-bold mb-4 d-flex align-items-center" style="color: #8b5cf6; border-bottom: 2px solid #f5f3ff; padding-bottom: 12px;">
                                    <i class="fas fa-user ms-2"></i> اطلاعات تحویل گیرنده (نیکوکار)
                                </h6>
                                <div class="row gy-4">
                                    <div class="col-md-4">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">نام و نام خانوادگی</span>
                                        <strong class="d-block text-dark" style="font-size: 15px;">{{ $allocation->applicant_name ?? $allocation->receiver_name ?? '---' }}</strong>
                                    </div>

                                    <!-- شماره موبایل با ترفند ویژه برای جلوگیری از به هم ریختگی اعداد -->
                                    <div class="col-md-4">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">شماره تماس</span>
                                        <div style="text-align: right;">
                                            <strong class="d-inline-block text-dark" style="font-size: 15px;" dir="ltr">{{ $allocation->applicant_mobile ?? $allocation->receiver_phone ?? '---' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">کد ملی</span>
                                        <div style="text-align: right;">
                                            <strong class="d-inline-block text-dark" style="font-size: 15px;" dir="ltr">{{ $allocation->applicant_national_code ?? '---' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">تاریخ تحویل صندوق</span>
                                        <strong class="d-block text-dark" style="font-size: 15px;">{{ $allocation->assigned_at ? verta($allocation->assigned_at)->format('Y/m/d ساعت H:i') : '---' }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">ثبت کننده در سیستم</span>
                                        <strong class="d-block text-dark" style="font-size: 15px;">{{ $allocation->registrar->name ?? '---' }}</strong>
                                    </div>
                                    <div class="col-12">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">آدرس دقیق</span>
                                        <div class="p-3 bg-white border text-dark" style="border-radius: 8px; line-height: 1.8; font-size: 14px;">
                                            {{ $allocation->applicant_address ?? 'آدرسی برای این نیکوکار ثبت نشده است.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- گروه 3: اطلاعات تخلیه (فقط برای بایگانی‌ها) -->
                            @if($isArchived)
                            <div class="p-4 border" style="background-color: #ecfdf5; border-radius: 12px; border-color: #a7f3d0 !important;">
                                <h6 class="fw-bold mb-4 d-flex align-items-center text-success" style="border-bottom: 2px solid #d1fae5; padding-bottom: 12px;">
                                    <i class="fas fa-check-double ms-2"></i> رسید تخلیه و تسویه
                                </h6>
                                <div class="row gy-4">
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">مبلغ جمع آوری شده</span>
                                        <strong class="d-block text-success fs-4">{{ number_format($allocation->amount) }} <span style="font-size: 13px; font-weight: normal;">ریال</span></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="d-block text-muted mb-2" style="font-size: 13px;">تاریخ و زمان دقیق تخلیه</span>
                                        <strong class="d-block text-dark" style="font-size: 15px;">{{ verta($allocation->collected_at)->format('Y/m/d ساعت H:i') }}</strong>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>

                        <!-- فوتر مودال -->
                        <div class="modal-footer border-top-0 d-flex justify-content-center" style="background-color: #f8fafc; padding: 1.2rem;">
                            <button type="button" class="btn btn-secondary px-5 py-2" data-bs-dismiss="modal" style="border-radius: 50px; font-size: 14px; background-color: #64748b; border: none;">
                                بستن پنجره
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- پایان مودال -->

        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #F8FAFC; border: 1px dashed #CBD5E1;">
                    <i class="fas fa-inbox" style="font-size: 30px; color: #94A3B8;"></i>
                </div>
                <h6 class="text-muted mb-0">هیچ صندوقی با این مشخصات یافت نشد!</h6>
            </div>
        </td>
    </tr>
@endforelse
